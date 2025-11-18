document.addEventListener('DOMContentLoaded', () => {
    const visiMisiBtns = document.querySelectorAll('.visi-misi-btn');
    const modal = document.getElementById('visi-misi-modal');
    const modalText = document.getElementById('modal-visi-misi-text');
    const closeModalBtn = document.getElementById('close-modal');
    const voteBtns = document.querySelectorAll('.vote-btn');

    // Logika untuk menampilkan modal Visi-Misi
    visiMisiBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const visiMisi = btn.getAttribute('data-visi-misi');
            modalText.textContent = visiMisi;
            modal.classList.remove('hidden');
        });
    });

    // Logika untuk menutup modal
    closeModalBtn.addEventListener('click', () => {
        modal.classList.add('hidden');
    });

    // Logika untuk tombol Vote (contoh dengan alert)
    voteBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const candidateId = btn.getAttribute('data-id');
            const confirmVote = confirm(`Apakah Anda yakin ingin memilih kandidat ini?`);
            
            if (confirmVote) {
                // Di sini Anda bisa mengirim data vote ke server menggunakan fetch()
                // Contoh:
                // fetch('vote_process.php', {
                //     method: 'POST',
                //     headers: { 'Content-Type': 'application/json' },
                //     body: JSON.stringify({ candidate_id: candidateId })
                // })
                // .then(response => response.json())
                // .then(data => {
                //     if(data.success) {
                //         alert('Vote berhasil!');
                //     } else {
                //         alert('Vote gagal: ' + data.message);
                //     }
                // });

                alert(`Anda telah memilih kandidat ${candidateId}!`);
                // Nonaktifkan tombol vote setelah diklik
                btn.disabled = true;
                btn.textContent = 'Sudah Memilih';
            }
        });
    });
});