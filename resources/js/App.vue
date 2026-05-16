<template>
  <q-layout view="hHh lpR fFf">
    <q-header elevated>
      <q-toolbar>
        <q-toolbar-title>
          Mini Marketplace
        </q-toolbar-title>

        <q-btn
          flat
          label="Dashboard"
          to="/"
        />

        <q-btn
          flat
          label="Products"
          to="/products"
        />

        <q-btn
          flat
          round
          dense
          icon="shopping_cart"
        />
      </q-toolbar>
    </q-header>

    <q-page-container>
      <router-view />
    </q-page-container>
  </q-layout>
</template>
