<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    requests: Array,
});

const page = usePage();
const user = computed(() => page.props.auth.user);
const isDispatcher = computed(() => user.value.role === 'dispatcher');
const isMaster = computed(() => user.value.role === 'master');

// Достаем возможные ошибки и сообщения
const flashSuccess = computed(() => page.props.flash?.success);
const errorConflict = computed(() => page.props.errors?.conflict);
const errorGeneral = computed(() => page.props.errors?.error);

// Вспомогательная функция для перевода статусов и цветов
const statusMap = {
    new: { label: 'Новая', color: 'bg-blue-100 text-blue-800' },
    assigned: { label: 'Назначена', color: 'bg-yellow-100 text-yellow-800' },
    in_progress: { label: 'В работе', color: 'bg-orange-100 text-orange-800' },
    done: { label: 'Выполнена', color: 'bg-green-100 text-green-800' },
    canceled: { label: 'Отменена', color: 'bg-red-100 text-red-800' },
};

// Экшены
const actionForm = useForm({ status: '' });

const takeToWork = (id) => {
    actionForm.post(route('requests.take', id), { preserveScroll: true });
};

const changeStatus = (id, newStatus) => {
    actionForm.status = newStatus;
    actionForm.patch(route('requests.update-status', id), { preserveScroll: true });
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Заявки ({{ isDispatcher ? 'Диспетчер' : 'Мастер' }})
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div v-if="flashSuccess" class="mb-4 p-4 bg-green-100 text-green-700 rounded-md shadow-sm">
                    {{ flashSuccess }}
                </div>
                <div v-if="errorConflict" class="mb-4 p-4 bg-red-100 text-red-700 rounded-md shadow-sm font-bold">
                    ⚠️ Ошибка гонки: {{ errorConflict }}
                </div>
                <div v-if="errorGeneral" class="mb-4 p-4 bg-red-100 text-red-700 rounded-md shadow-sm">
                    {{ errorGeneral }}
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="req in requests" :key="req.id" class="bg-white overflow-hidden shadow-sm sm:rounded-lg flex flex-col">
                        <div class="p-6 flex-grow">
                            <div class="flex justify-between items-start mb-4">
                                <span class="text-xs font-bold text-gray-400">#{{ req.id }}</span>
                                <span :class="['px-2 py-1 text-xs rounded-full font-semibold', statusMap[req.status].color]">
                                    {{ statusMap[req.status].label }}
                                </span>
                            </div>
                            
                            <h3 class="font-bold text-lg text-gray-900 mb-1">{{ req.problem_text }}</h3>
                            
                            <div class="text-sm text-gray-600 mt-4 space-y-2">
                                <p><span class="font-semibold">Клиент:</span> {{ req.client_name }}</p>
                                <p><span class="font-semibold">Телефон:</span> {{ req.phone }}</p>
                                <p><span class="font-semibold">Адрес:</span> {{ req.address }}</p>
                                <p v-if="req.master" class="text-indigo-600 mt-2">
                                    <span class="font-semibold">Мастер:</span> {{ req.master.name }}
                                </p>
                            </div>
                        </div>

                        <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex justify-end gap-2">
                            
                            <template v-if="isDispatcher">
                                <button v-if="req.status !== 'canceled' && req.status !== 'done'" 
                                        @click="changeStatus(req.id, 'canceled')" 
                                        class="text-sm text-red-600 hover:text-red-800 font-semibold transition-colors disabled:opacity-50"
                                        :disabled="actionForm.processing">
                                    Отменить заявку
                                </button>
                                <span v-else class="text-sm text-gray-400">Нет действий</span>
                            </template>

                            <template v-if="isMaster">
                                <button v-if="req.status === 'new'" 
                                        @click="takeToWork(req.id)" 
                                        class="px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded hover:bg-indigo-700 transition-colors disabled:opacity-50"
                                        :disabled="actionForm.processing">
                                    Взять в работу
                                </button>

                                <button v-if="req.status === 'assigned' && req.assigned_to === user.id" 
                                        @click="changeStatus(req.id, 'in_progress')" 
                                        class="px-4 py-2 bg-yellow-500 text-white text-sm font-semibold rounded hover:bg-yellow-600 transition-colors disabled:opacity-50"
                                        :disabled="actionForm.processing">
                                    Начать работу
                                </button>

                                <button v-if="req.status === 'in_progress' && req.assigned_to === user.id" 
                                        @click="changeStatus(req.id, 'done')" 
                                        class="px-4 py-2 bg-green-500 text-white text-sm font-semibold rounded hover:bg-green-600 transition-colors disabled:opacity-50"
                                        :disabled="actionForm.processing">
                                    Завершить
                                </button>
                                
                                <span v-if="req.status === 'done' || req.status === 'canceled'" class="text-sm text-gray-400">Завершена</span>
                            </template>

                        </div>
                    </div>
                </div>
                
                <div v-if="requests.length === 0" class="text-center py-12 bg-white rounded-lg shadow-sm text-gray-500">
                    Нет доступных заявок.
                </div>
                
            </div>
        </div>
    </AuthenticatedLayout>
</template>