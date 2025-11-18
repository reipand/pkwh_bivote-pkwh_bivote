<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background-color: #f3f4f6;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-sm">
        <h2 class="text-2xl font-bold text-center mb-6 text-gray-800">Admin Login</h2>
        
        <div id="feedback-message" class="mb-4 text-center text-sm font-medium"></div>

        <form id="admin-login-form" method="POST" action="../api/login_admin.php">
            <div class="mb-4">
                <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                <input type="text" id="username" name="username" required 
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="mb-6">
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" id="password" name="password" required 
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <button type="submit" 
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Log In
            </button>
        </form>
    </div>

    <script>
        document.getElementById('admin-login-form').addEventListener('submit', function(e) {
            e.preventDefault();

            const form = e.target;
            const feedbackMessage = document.getElementById('feedback-message');
            const formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Jaringan bermasalah atau server error');
                }
                return response.json();
            })
            .then(data => {
                feedbackMessage.textContent = '';
                feedbackMessage.classList.remove('text-green-600', 'text-red-600');
                if (data.status === 'success') {
                    feedbackMessage.textContent = data.message;
                    feedbackMessage.classList.add('text-green-600');
                    setTimeout(() => {
                        window.location.href = 'dashboard_admin.php';
                    }, 1000);
                } else {
                    feedbackMessage.textContent = data.message;
                    feedbackMessage.classList.add('text-red-600');
                }
            })
            .catch(error => {
                feedbackMessage.textContent = 'Terjadi kesalahan saat menghubungi server.';
                feedbackMessage.classList.add('text-red-600');
                console.error('Error:', error);
            });
        });
    </script>
</body>
</html>