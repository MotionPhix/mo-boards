<template>
  <AppLayout title="Payment Checkout">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Purchase Template
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-6">
            <!-- Template Details -->
            <div class="mb-8 p-6 bg-gray-50 rounded-lg">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Template Details</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <p class="text-sm font-medium text-gray-500">Template Name</p>
                  <p class="text-sm text-gray-900">{{ template.name }}</p>
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-500">Category</p>
                  <p class="text-sm text-gray-900">{{ template.category }}</p>
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-500">Price</p>
                  <p class="text-lg font-bold text-green-600">
                    MWK {{ formatCurrency(template.price) }}
                  </p>
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-500">Features</p>
                  <div class="flex flex-wrap gap-1 mt-1">
                    <span
                      v-for="feature in template.features"
                      :key="feature"
                      class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                    >
                      {{ feature }}
                    </span>
                  </div>
                </div>
              </div>

              <div class="mt-4">
                <p class="text-sm font-medium text-gray-500 mb-2">Description</p>
                <p class="text-sm text-gray-700">{{ template.description }}</p>
              </div>
            </div>

            <!-- Company Details -->
            <div class="mb-8 p-6 bg-blue-50 rounded-lg">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Billing Information</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <p class="text-sm font-medium text-gray-500">Company</p>
                  <p class="text-sm text-gray-900">{{ company.name }}</p>
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-500">Email</p>
                  <p class="text-sm text-gray-900">{{ user.email }}</p>
                </div>
              </div>
            </div>

            <!-- Payment Method Selection -->
            <form @submit.prevent="processPayment" class="space-y-6">
              <div>
                <label class="text-base font-medium text-gray-900">Payment Method</label>
                <p class="text-sm leading-5 text-gray-500">Select your preferred payment method</p>
                <fieldset class="mt-4">
                  <div class="space-y-4">
                    <div class="flex items-center">
                      <input
                        id="mobile_money"
                        v-model="form.payment_method"
                        name="payment_method"
                        type="radio"
                        value="mobile_money"
                        class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300"
                      />
                      <label for="mobile_money" class="ml-3 block text-sm font-medium text-gray-700">
                        <div class="flex items-center">
                          <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                              <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
                            </svg>
                          </div>
                          <div class="ml-2">
                            <div class="text-sm font-medium text-gray-900">Mobile Money</div>
                            <div class="text-sm text-gray-500">Pay with Airtel Money, TNM Mpamba</div>
                          </div>
                        </div>
                      </label>
                    </div>

                    <div class="flex items-center">
                      <input
                        id="bank_card"
                        v-model="form.payment_method"
                        name="payment_method"
                        type="radio"
                        value="bank_card"
                        class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300"
                      />
                      <label for="bank_card" class="ml-3 block text-sm font-medium text-gray-700">
                        <div class="flex items-center">
                          <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                              <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                            </svg>
                          </div>
                          <div class="ml-2">
                            <div class="text-sm font-medium text-gray-900">Bank Card</div>
                            <div class="text-sm text-gray-500">Pay with Visa, Mastercard</div>
                          </div>
                        </div>
                      </label>
                    </div>
                  </div>
                </fieldset>
                <div v-if="form.errors.payment_method" class="mt-1 text-sm text-red-600">
                  {{ form.errors.payment_method }}
                </div>
              </div>

              <!-- Terms and Conditions -->
              <div class="flex items-start">
                <div class="flex items-center h-5">
                  <input
                    id="agree_terms"
                    v-model="form.agree_terms"
                    name="agree_terms"
                    type="checkbox"
                    class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded"
                  />
                </div>
                <div class="ml-3 text-sm">
                  <label for="agree_terms" class="font-medium text-gray-700">
                    I agree to the terms and conditions
                  </label>
                  <p class="text-gray-500">
                    By purchasing this template, you agree to our
                    <a href="#" class="text-blue-600 hover:text-blue-500">Terms of Service</a>
                    and
                    <a href="#" class="text-blue-600 hover:text-blue-500">Privacy Policy</a>.
                  </p>
                </div>
              </div>
              <div v-if="form.errors.agree_terms" class="text-sm text-red-600">
                {{ form.errors.agree_terms }}
              </div>

              <!-- Purchase Summary -->
              <div class="bg-gray-50 rounded-lg p-6">
                <h4 class="text-lg font-medium text-gray-900 mb-4">Purchase Summary</h4>
                <div class="space-y-2">
                  <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Template Price</span>
                    <span class="text-gray-900">MWK {{ formatCurrency(template.price) }}</span>
                  </div>
                  <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Processing Fee</span>
                    <span class="text-gray-900">Included</span>
                  </div>
                  <div class="border-t border-gray-200 pt-2">
                    <div class="flex justify-between text-base font-medium">
                      <span class="text-gray-900">Total</span>
                      <span class="text-gray-900">MWK {{ formatCurrency(template.price) }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="flex justify-between pt-6">
                <Link
                  :href="route('contract-templates.marketplace')"
                  class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                  <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                  </svg>
                  Back to Marketplace
                </Link>

                <Button
                  type="submit"
                  :disabled="form.processing || !form.payment_method || !form.agree_terms"
                  :class="{ 'opacity-25': form.processing }"
                >
                  <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  {{ form.processing ? 'Processing...' : `Pay MWK ${formatCurrency(template.price)}` }}
                </Button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';

interface Template {
  id: number;
  name: string;
  description: string;
  price: number;
  category: string;
  features: string[];
}

interface Transaction {
  id: number;
  reference: string;
  amount: number;
  currency: string;
}

interface Company {
  id: number;
  name: string;
}

interface User {
  id: number;
  name: string;
  email: string;
}

interface Props {
  template: Template;
  transaction: Transaction;
  company: Company;
  user: User;
}

const props = defineProps<Props>();

const form = useForm({
  transaction_id: props.transaction.id,
  payment_method: '',
  agree_terms: false,
});

const formatCurrency = (amount: number): string => {
  return new Intl.NumberFormat('en-MW', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(amount);
};

const processPayment = () => {
  if (!form.agree_terms) {
    form.setError('agree_terms', 'You must agree to the terms and conditions');
    return;
  }

  form.post(route('contract-templates.process-payment', props.template.id), {
    onSuccess: () => {
      // This will redirect to PayChangu checkout
    },
    onError: (errors) => {
      console.error('Payment processing error:', errors);
    },
  });
};
</script>
