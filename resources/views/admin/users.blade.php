<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Users</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 font-sans antialiased">
    <div class="min-h-screen p-6 lg:p-12 transition-colors duration-300">
        <div class="max-w-7xl mx-auto">
            <header class="mb-8 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">User Management</h1>
                    <p class="text-gray-500 dark:text-gray-400 mt-2">View all registered users and their activation status.</p>
                </div>
                <a href="/" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-medium transition-colors">
                    &larr; Back to Home
                </a>
            </header>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden border border-gray-200 dark:border-gray-700">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-750 border-b border-gray-200 dark:border-gray-700">
                                <th class="px-6 py-4 font-semibold text-gray-900 dark:text-white uppercase tracking-wider text-xs">Name</th>
                                <th class="px-6 py-4 font-semibold text-gray-900 dark:text-white uppercase tracking-wider text-xs">Email</th>
                                <th class="px-6 py-4 font-semibold text-gray-900 dark:text-white uppercase tracking-wider text-xs">Joined</th>
                                <th class="px-6 py-4 font-semibold text-gray-900 dark:text-white uppercase tracking-wider text-xs">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($users as $user)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                                <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                    {{ $user->name }}
                                </td>
                                <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                                    {{ $user->email }}
                                </td>
                                <td class="px-6 py-4 text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                    {{ $user->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4">
                                    @if($user->biometric_credentials_count > 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                            <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5"></span>
                                            Activated
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                            <span class="w-1.5 h-1.5 bg-gray-400 rounded-full mr-1.5"></span>
                                            Pending
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                    No users found.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="mt-4 text-center text-xs text-gray-400 dark:text-gray-600">
                Found {{ $users->count() }} total users.
            </div>
        </div>
    </div>
</body>
</html>
