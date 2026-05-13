<template>
  <q-page padding>
    <div class="row q-col-gutter-md">
      <div class="col-12 col-md-8">
        <q-card flat bordered>
          <q-card-section>
            <div class="text-h5">Mini Marketplace</div>
            <div class="text-body2 text-grey-7 q-mt-sm">
              Laravel 12 + PHP 8.4 + PostgreSQL + Redis + Quasar 2.
            </div>
          </q-card-section>

          <q-separator />

          <q-card-section>
            <q-btn
              color="primary"
              label="Quasar подключен"
              icon="check_circle"
              @click="notify"
            />
          </q-card-section>
        </q-card>
      </div>
    </div>
  </q-page>
</template>

<script setup>
import { useQuasar } from 'quasar';

const $q = useQuasar();

const notify = () => {
  $q.notify({
    type: 'positive',
    message: 'Frontend работает',
  });
};
</script>
