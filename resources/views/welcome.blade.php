<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="antialiased bg-gray-100 text-gray-900">

    <div class="min-h-screen flex flex-col py-10 px-6 lg:px-12">
        <header class="flex justify-between items-center mb-10">
            <h1 class="text-3xl font-bold">Admin Dashboard</h1>
            <nav class="space-x-4">
                <a href="#" class="text-gray-600 hover:text-black">Home</a>
                <a href="#" class="text-gray-600 hover:text-black">Settings</a>
                <a href="#" class="text-red-600 hover:text-red-700 font-semibold">Logout</a>
            </nav>
        </header>

        <div class="mx-auto w-full max-w-7xl text-center mb-12">
            <p class="text-lg text-gray-600">Welcome to your admin panel — manage everything from one place.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white p-6 rounded-xl shadow">
                <h2 class="text-xl font-semibold">Users</h2>
                <p class="text-gray-600">Manage registered users</p>
            </div>

            <div class="bg-white p-6 rounded-xl shadow">
                <h2 class="text-xl font-semibold">Orders</h2>
                <p class="text-gray-600">Track customer orders</p>
            </div>

            <div class="bg-white p-6 rounded-xl shadow">
                <h2 class="text-xl font-semibold">Analytics</h2>
                <p class="text-gray-600">View system analytics</p>
            </div>

            <div class="bg-white p-6 rounded-xl shadow">
                <h2 class="text-xl font-semibold">Settings</h2>
                <p class="text-gray-600">Manage configuration</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-12">
            <div class="bg-white p-6 rounded-xl shadow text-center">
                <h3 class="text-gray-700">Daily Visitors</h3>
                <p class="text-4xl font-bold">1,248</p>
            </div>

            <div class="bg-white p-6 rounded-xl shadow text-center">
                <h3 class="text-gray-700">Monthly Sales</h3>
                <p class="text-4xl font-bold">$8,432</p>
            </div>

            <div class="bg-white p-6 rounded-xl shadow text-center">
                <h3 class="text-gray-700">Pending Tasks</h3>
                <p class="text-4xl font-bold">12</p>
            </div>
        </div>

        <footer class="text-center mt-12 text-gray-500 text-sm">
            © 2025 Admin Dashboard. All rights reserved.
        </footer>
    </div>

</body>
</html>
