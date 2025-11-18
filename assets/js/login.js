document.addEventListener('DOMContentLoaded', () => {
    const loginFormSiswa = document.getElementById('login-form-siswa');
    const loginFormGuru = document.getElementById('login-form-guru');
    const nisInput = document.getElementById('nis');
    const passwordInput = document.getElementById('password');
    const nikInput = document.getElementById('nik');
    const passwordGuruInput = document.getElementById('password_guru');
    const loginBtnSiswa = document.getElementById('login-btn-siswa');
    const loginBtnGuru = document.getElementById('login-btn-guru');
    const feedbackMessage = document.getElementById('feedback-message');

    // Handler untuk form siswa
    if (loginFormSiswa) {
        loginFormSiswa.addEventListener('submit', async (e) => {
            e.preventDefault();

            feedbackMessage.textContent = '';
            feedbackMessage.classList.remove('text-red-500', 'text-green-500');
            feedbackMessage.className = 'text-sm mb-2'; // Reset class untuk styling

            loginBtnSiswa.disabled = true;
            loginBtnSiswa.textContent = 'Memproses...';

            if (nisInput.value.trim() === '' || passwordInput.value.trim() === '') {
                feedbackMessage.innerHTML = '<div class="p-2 text-sm text-red-700 bg-red-100 rounded-lg">NIS dan Password tidak boleh kosong.</div>';
                loginBtnSiswa.disabled = false;
                loginBtnSiswa.textContent = 'LOGIN SISWA';
                return;
            }

            const formData = new FormData(loginFormSiswa);
            // --- PERUBAHAN UTAMA UNTUK KOMPATIBILITAS ---
            const bodyParams = new URLSearchParams(formData);

            try {
                const response = await fetch('../api/login_siswa.php', {
                    method: 'POST',
                    body: bodyParams // Menggunakan URLSearchParams
                });

                if (!response.ok) {
                    throw new Error('Jaringan bermasalah atau server error');
                }

                const data = await response.json();

                if (data.status === 'success' && data.redirect) {
                    // Redirect berhasil
                    window.location.href = data.redirect;
                } else {
                    // Menampilkan pesan error dari PHP
                    feedbackMessage.innerHTML = `<div class="p-2 text-sm text-red-700 bg-red-100 rounded-lg">${data.message || 'Terjadi kesalahan tidak dikenal.'}</div>`;
                }
            } catch (error) {
                console.error('AJAX Error:', error);
                feedbackMessage.innerHTML = '<div class="p-2 text-sm text-red-700 bg-red-100 rounded-lg">Terjadi kesalahan saat menghubungi server. Silakan coba lagi.</div>';
            } finally {
                loginBtnSiswa.disabled = false;
                loginBtnSiswa.textContent = 'LOGIN SISWA';
            }
        });
    }

    // Handler untuk form guru
    if (loginFormGuru) {
        loginFormGuru.addEventListener('submit', async (e) => {
            e.preventDefault();

            feedbackMessage.textContent = '';
            feedbackMessage.classList.remove('text-red-500', 'text-green-500');
            feedbackMessage.className = 'text-sm mb-2'; // Reset class untuk styling

            loginBtnGuru.disabled = true;
            loginBtnGuru.textContent = 'Memproses...';

            if (nikInput.value.trim() === '' || passwordGuruInput.value.trim() === '') {
                feedbackMessage.innerHTML = '<div class="p-2 text-sm text-red-700 bg-red-100 rounded-lg">NIK dan Password tidak boleh kosong.</div>';
                loginBtnGuru.disabled = false;
                loginBtnGuru.textContent = 'LOGIN GURU';
                return;
            }

            const formData = new FormData(loginFormGuru);
            // --- PERUBAHAN UTAMA UNTUK KOMPATIBILITAS ---
            const bodyParams = new URLSearchParams(formData);

            try {
                const response = await fetch('../api/login_guru.php', {
                    method: 'POST',
                    body: bodyParams // Menggunakan URLSearchParams
                });

                if (!response.ok) {
                    throw new Error('Jaringan bermasalah atau server error');
                }

                const data = await response.json();

                if (data.status === 'success' && data.redirect) {
                    // Redirect berhasil
                    window.location.href = data.redirect;
                } else {
                    // Menampilkan pesan error dari PHP
                    feedbackMessage.innerHTML = `<div class="p-2 text-sm text-red-700 bg-red-100 rounded-lg">${data.message || 'Terjadi kesalahan tidak dikenal.'}</div>`;
                }
            } catch (error) {
                console.error('AJAX Error:', error);
                feedbackMessage.innerHTML = '<div class="p-2 text-sm text-red-700 bg-red-100 rounded-lg">Terjadi kesalahan saat menghubungi server. Silakan coba lagi.</div>';
            } finally {
                loginBtnGuru.disabled = false;
                loginBtnGuru.textContent = 'LOGIN GURU';
            }
        });
    }
});