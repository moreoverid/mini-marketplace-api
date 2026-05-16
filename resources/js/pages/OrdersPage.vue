<template>
  <q-page padding>
    <div class="row items-center justify-between q-mb-md">
      <div>
        <div class="text-h5">Orders</div>
        <div class="text-body2 text-grey-7">
          Create orders, filter by status and pay pending orders.
        </div>
      </div>

      <div class="row q-gutter-sm">
        <q-btn
          color="primary"
          icon="add"
          label="Create order"
          @click="openCreateDialog"
        />

        <q-btn
          outline
          icon="refresh"
          label="Refresh"
          :loading="loading"
          @click="loadOrders"
        />
      </div>
    </div>

    <q-card flat bordered>
      <q-card-section>
        <q-select
          v-model="status"
          outlined
          dense
          clearable
          emit-value
          map-options
          label="Status"
          :options="statusOptions"
          @update:model-value="onStatusChange"
        />
      </q-card-section>

      <q-separator />

      <q-table
        v-model:pagination="pagination"
        flat
        :rows="orders"
        :columns="columns"
        row-key="id"
        :loading="loading"
        :rows-per-page-options="[5, 10, 15, 25, 50]"
        binary-state-sort
        @request="onTableRequest"
      >
        <template #body-cell-id="props">
          <q-td :props="props">
            <span class="text-monospace">
              {{ shortId(props.row.id) }}
            </span>
          </q-td>
        </template>

        <template #body-cell-status="props">
          <q-td :props="props">
            <q-badge :color="statusColor(props.row.status)">
              {{ props.row.status }}
            </q-badge>
          </q-td>
        </template>

        <template #body-cell-total="props">
          <q-td :props="props">
            {{ formatMoney(props.row.total.amount, props.row.total.currency) }}
          </q-td>
        </template>

        <template #body-cell-created_at="props">
          <q-td :props="props">
            {{ formatDate(props.row.created_at) }}
          </q-td>
        </template>

        <template #body-cell-actions="props">
          <q-td :props="props">
            <q-btn
              v-if="props.row.status === 'pending'"
              dense
              color="primary"
              label="Pay"
              :loading="payingOrderId === props.row.id"
              @click="pay(props.row.id)"
            />

            <span v-else class="text-grey-6">—</span>
          </q-td>
        </template>
      </q-table>
    </q-card>

    <q-dialog v-model="isCreateDialogOpen">
      <q-card style="width: 560px; max-width: 95vw;">
        <q-card-section>
          <div class="text-h6">Create order</div>
          <div class="text-body2 text-grey-7 q-mt-xs">
            Select a product and quantity.
          </div>
        </q-card-section>

        <q-separator />

        <q-form @submit.prevent="submitCreateOrder">
          <q-card-section class="q-gutter-md">
            <q-select
              v-model="form.product_id"
              outlined
              label="Product"
              :options="productOptions"
              emit-value
              map-options
              :loading="loadingProducts"
              :error="hasError('items.0.product_id')"
              :error-message="firstError('items.0.product_id')"
            />

            <q-input
              v-model.number="form.quantity"
              outlined
              type="number"
              label="Quantity"
              min="1"
              :error="hasError('items.0.quantity')"
              :error-message="firstError('items.0.quantity')"
            />
          </q-card-section>

          <q-separator />

          <q-card-actions align="right">
            <q-btn
              flat
              label="Cancel"
              :disable="creating"
              @click="isCreateDialogOpen = false"
            />

            <q-btn
              color="primary"
              type="submit"
              label="Create"
              :loading="creating"
            />
          </q-card-actions>
        </q-form>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import { date, useQuasar } from 'quasar';
import { createOrder, fetchOrders, payOrder } from '../api/orders';
import { fetchProducts } from '../api/products';

const $q = useQuasar();

const orders = ref([]);
const products = ref([]);

const loading = ref(false);
const loadingProducts = ref(false);
const creating = ref(false);
const payingOrderId = ref(null);

const status = ref(null);
const isCreateDialogOpen = ref(false);
const validationErrors = ref({});

const pagination = ref({
  page: 1,
  rowsPerPage: 10,
  rowsNumber: 0,
});

const form = reactive({
  product_id: null,
  quantity: 1,
});

const statusOptions = [
  { label: 'Pending', value: 'pending' },
  { label: 'Paid', value: 'paid' },
  { label: 'Cancelled', value: 'cancelled' },
];

const columns = [
  {
    name: 'id',
    label: 'Order ID',
    field: 'id',
    align: 'left',
  },
  {
    name: 'status',
    label: 'Status',
    field: 'status',
    align: 'left',
  },
  {
    name: 'items_count',
    label: 'Items',
    field: 'items_count',
    align: 'right',
  },
  {
    name: 'total',
    label: 'Total',
    field: 'total',
    align: 'left',
  },
  {
    name: 'created_at',
    label: 'Created',
    field: 'created_at',
    align: 'left',
  },
  {
    name: 'actions',
    label: 'Actions',
    field: 'actions',
    align: 'right',
  },
];

const productOptions = computed(() => products.value.map((product) => ({
  label: `${product.name} — ${formatMoney(product.price.amount, product.price.currency)} | stock: ${product.stock}`,
  value: product.id,
})));

async function loadOrders() {
  loading.value = true;

  try {
    const response = await fetchOrders({
      page: pagination.value.page,
      per_page: pagination.value.rowsPerPage,
      status: status.value || undefined,
    });

    orders.value = response.data;

    pagination.value = {
      ...pagination.value,
      rowsNumber: response.meta.total,
      rowsPerPage: response.meta.per_page,
      page: response.meta.current_page,
    };
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Failed to load orders',
    });
  } finally {
    loading.value = false;
  }
}

async function loadProducts() {
  loadingProducts.value = true;

  try {
    const response = await fetchProducts({
      page: 1,
      per_page: 100,
    });

    products.value = response.data;
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Failed to load products',
    });
  } finally {
    loadingProducts.value = false;
  }
}

async function openCreateDialog() {
  validationErrors.value = {};

  form.product_id = null;
  form.quantity = 1;

  isCreateDialogOpen.value = true;

  await loadProducts();
}

async function submitCreateOrder() {
  creating.value = true;
  validationErrors.value = {};

  try {
    await createOrder({
      items: [
        {
          product_id: form.product_id,
          quantity: Number(form.quantity),
        },
      ],
    });

    isCreateDialogOpen.value = false;

    $q.notify({
      type: 'positive',
      message: 'Order created',
    });

    await loadOrders();
  } catch (error) {
    if (error.response?.status === 422) {
      validationErrors.value = error.response.data.errors || {};

      return;
    }

    $q.notify({
      type: 'negative',
      message: 'Failed to create order',
    });
  } finally {
    creating.value = false;
  }
}

async function pay(orderId) {
  payingOrderId.value = orderId;

  try {
    await payOrder(orderId);

    $q.notify({
      type: 'positive',
      message: 'Order paid',
    });

    await loadOrders();
  } catch (error) {
    if (error.response?.status === 409) {
      $q.notify({
        type: 'warning',
        message: error.response.data.message || 'Order cannot be paid',
      });

      await loadOrders();

      return;
    }

    $q.notify({
      type: 'negative',
      message: 'Failed to pay order',
    });
  } finally {
    payingOrderId.value = null;
  }
}

function onTableRequest(props) {
  pagination.value = props.pagination;

  loadOrders();
}

function onStatusChange() {
  pagination.value.page = 1;

  loadOrders();
}

function hasError(field) {
  return Boolean(validationErrors.value[field]?.length);
}

function firstError(field) {
  return validationErrors.value[field]?.[0] || '';
}

function shortId(id) {
  return `${id.slice(0, 8)}...${id.slice(-4)}`;
}

function formatMoney(amount, currency) {
  return `${amount} ${currency}`;
}

function formatDate(value) {
  if (!value) {
    return '—';
  }

  return date.formatDate(value, 'YYYY-MM-DD HH:mm');
}

function statusColor(value) {
  if (value === 'paid') {
    return 'positive';
  }

  if (value === 'cancelled') {
    return 'negative';
  }

  return 'warning';
}

onMounted(() => {
  loadOrders();
});
</script>
