<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const flashSuccess = computed(() => page.props.flash?.success);

const form = useForm({
    client_name: '',
    phone: '',
    address: '',
    problem_text: '',
});

const submit = () => {
    form.post(route('requests.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Оставить заявку" />

    <div class="min-h-screen bg-gray-100 selection:bg-red-500 selection:text-white">
        <div class="p-6 text-right z-10 w-full flex justify-between items-center bg-white shadow-sm">
            <div class="text-xl font-bold text-gray-800">РемонтСервис</div>
            <div>
                <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="font-semibold text-gray-600 hover:text-gray-900 focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">
                    Панель управления
                </Link>
                <Link v-else :href="route('login')" class="font-semibold text-gray-600 hover:text-gray-900 focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">
                    Вход для сотрудников
                </Link>
            </div>
        </div>

        <div class="max-w-2xl mx-auto p-6 lg:p-8 mt-10">
            <div class="bg-white p-8 rounded-lg shadow-md">
                <h1 class="text-2xl font-bold mb-6 text-gray-800">Оставить заявку на ремонт</h1>

                <div v-if="flashSuccess" class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-md">
                    {{ flashSuccess }}
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label for="client_name" class="block text-sm font-medium text-gray-700">Ваше имя</label>
                        <input id="client_name" v-model="form.client_name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required />
                        <div v-if="form.errors.client_name" class="text-red-600 text-sm mt-1">{{ form.errors.client_name }}</div>
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700">Телефон</label>
                        <input id="phone" v-model="form.phone" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required />
                        <div v-if="form.errors.phone" class="text-red-600 text-sm mt-1">{{ form.errors.phone }}</div>
                    </div>

                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700">Адрес</label>
                        <input id="address" v-model="form.address" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required />
                        <div v-if="form.errors.address" class="text-red-600 text-sm mt-1">{{ form.errors.address }}</div>
                    </div>

                    <div>
                        <label for="problem_text" class="block text-sm font-medium text-gray-700">Описание проблемы</label>
                        <textarea id="problem_text" v-model="form.problem_text" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required></textarea>
                        <div v-if="form.errors.problem_text" class="text-red-600 text-sm mt-1">{{ form.errors.problem_text }}</div>
                    </div>

                    <div class="pt-4">
                        <button type="submit" :disabled="form.processing" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                            {{ form.processing ? 'Отправка...' : 'Отправить заявку' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>