<template>
  <q-page padding>
    <div class="row items-center justify-between q-mb-md">
      <div>
        <div class="text-h5">Products</div>
        <div class="text-body2 text-grey-7">
          Catalog read side powered by PostgreSQL list and Elasticsearch search.
        </div>
      </div>

      <q-btn
        color="primary"
        icon="add"
        label="Create product"
        @click="openCreateDialog"
      />
    </div>

    <q-card flat bordered>
      <q-card-section>
        <q-input
          v-model="search"
          outlined
          dense
          debounce="400"
          placeholder="Search products..."
          clearable
          @update:model-value="onSearchChange"
        >
          <template #prepend>
            <q-icon name="search" />
          </template>
        </q-input>
      </q-card-section>

      <q-separator />

      <q-table
        v-model:pagination="pagination"
        flat
        :rows="products"
        :columns="columns"
        row-key="id"
        :loading="loading"
        :rows-per-page-options="[5, 10, 15, 25, 50]"
        binary-state-sort
        @request="onTableRequest"
      >
        <template #body-cell-price="props">
          <q-td :props="props">
            {{ formatMoney(props.row.price.amount, props.row.price.currency) }}
          </q-td>
        </template>

        <template #body-cell-created_at="props">
          <q-td :props="props">
            {{ formatDate(props.row.created_at) }}
          </q-td>
        </template>
      </q-table>
    </q-card>

    <q-dialog v-model="isCreateDialogOpen">
      <q-card style="width: 520px; max-width: 95vw;">
        <q-card-section>
          <div class="text-h6">Create product</div>
        </q-card-section>

        <q-separator />

        <q-form @submit.prevent="submitCreateProduct">
          <q-card-section class="q-gutter-md">
            <q-input
              v-model="form.name"
              outlined
              label="Name"
              :error="hasError('name')"
              :error-message="firstError('name')"
            />

            <q-input
              v-model.number="form.price_amount"
              outlined
              type="number"
              label="Price amount"
              hint="Stored in minor units, for example 99900"
              :error="hasError('price_amount')"
              :error-message="firstError('price_amount')"
            />

            <q-input
              v-model="form.currency"
              outlined
              label="Currency"
              maxlength="3"
              :error="hasError('currency')"
              :error-message="firstError('currency')"
            />

            <q-input
              v-model.number="form.stock"
              outlined
              type="number"
              label="Stock"
              :error="hasError('stock')"
              :error-message="firstError('stock')"
            />
          </q-card-section>

          <q-separator />

          <q-card-actions align="right">
            <q-btn
              flat
              label="Cancel"
              :disable="saving"
              @click="isCreateDialogOpen = false"
            />

            <q-btn
              color="primary"
              type="submit"
              label="Create"
              :loading="saving"
            />
          </q-card-actions>
        </q-form>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import { useQuasar, date } from 'quasar';
import { createProduct, fetchProducts, searchProducts } from '../api/products';

const $q = useQuasar();

const products = ref([]);
const loading = ref(false);
const saving = ref(false);
const search = ref('');
const isCreateDialogOpen = ref(false);
const validationErrors = ref({});

const pagination = ref({
  page: 1,
  rowsPerPage: 10,
  rowsNumber: 0,
});

const form = reactive({
  name: '',
  price_amount: 0,
  currency: 'USD',
  stock: 0,
});

const columns = [
  {
    name: 'name',
    label: 'Name',
    field: 'name',
    align: 'left',
  },
  {
    name: 'price',
    label: 'Price',
    field: 'price',
    align: 'left',
  },
  {
    name: 'stock',
    label: 'Stock',
    field: 'stock',
    align: 'right',
  },
  {
    name: 'created_at',
    label: 'Created',
    field: 'created_at',
    align: 'left',
  },
];

async function loadProducts() {
  loading.value = true;

  try {
    const hasSearch = Boolean(search.value?.trim());

    const response = hasSearch
      ? await searchProducts({
        page: pagination.value.page,
        per_page: pagination.value.rowsPerPage,
        query: search.value.trim(),
      })
      : await fetchProducts({
        page: pagination.value.page,
        per_page: pagination.value.rowsPerPage,
      });

    products.value = response.data;

    pagination.value = {
      ...pagination.value,
      rowsNumber: response.meta.total,
      rowsPerPage: response.meta.per_page,
      page: response.meta.current_page,
    };
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Failed to load products',
    });
  } finally {
    loading.value = false;
  }
}

function onTableRequest(props) {
  pagination.value = props.pagination;

  loadProducts();
}

function onSearchChange() {
  pagination.value.page = 1;

  loadProducts();
}

function openCreateDialog() {
  validationErrors.value = {};

  form.name = '';
  form.price_amount = 0;
  form.currency = 'USD';
  form.stock = 0;

  isCreateDialogOpen.value = true;
}

async function submitCreateProduct() {
  saving.value = true;
  validationErrors.value = {};

  try {
    await createProduct({
      name: form.name,
      price_amount: form.price_amount,
      currency: form.currency,
      stock: form.stock,
    });

    isCreateDialogOpen.value = false;

    $q.notify({
      type: 'positive',
      message: 'Product created',
    });

    await loadProducts();
  } catch (error) {
    if (error.response?.status === 422) {
      validationErrors.value = error.response.data.errors || {};

      return;
    }

    $q.notify({
      type: 'negative',
      message: 'Failed to create product',
    });
  } finally {
    saving.value = false;
  }
}

function hasError(field) {
  return Boolean(validationErrors.value[field]?.length);
}

function firstError(field) {
  return validationErrors.value[field]?.[0] || '';
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

onMounted(() => {
  loadProducts();
});
</script>
