<div class="space-y-2">
    @foreach($users as $user)
        <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-primary-100 dark:bg-primary-900 rounded-full flex items-center justify-center">
                    <span class="text-sm font-medium text-primary-600 dark:text-primary-400">
                        {{ mb_substr($user->name, 0, 1) }}
                    </span>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->name }}</p>
                    <p class="text-xs text-gray-500">{{ $user->email }}</p>
                </div>
            </div>
            <span class="px-2 py-1 text-xs font-medium rounded-full {{ match($user->role) {
                'admin' => 'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-400',
                'manager' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/50 dark:text-yellow-400',
                default => 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-400',
            } }}">
                {{ match($user->role) {
                    'admin' => 'مسؤول',
                    'manager' => 'مدير فرع',
                    default => 'موظف',
                } }}
            </span>
        </div>
    @endforeach
</div>
