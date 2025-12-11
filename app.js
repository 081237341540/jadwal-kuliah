// assets/js/app.js

document.addEventListener("DOMContentLoaded", function () {

    // ==========================
    // 1. KONFIRMASI HAPUS
    // ==========================
    // Tambahkan class .js-confirm-delete di tombol/anchor Hapus
    const deleteButtons = document.querySelectorAll(".js-confirm-delete");

    deleteButtons.forEach(function (btn) {
        btn.addEventListener("click", function (e) {
            const item = btn.getAttribute("data-item") || "data ini";
            const ok = confirm("Yakin ingin menghapus " + item + " ?");
            if (!ok) {
                e.preventDefault();
            }
        });
    });


    // ==========================
    // 2. KONFIRMASI LOGOUT
    // ==========================
    // Tambahkan id="btn-logout" di tombol Keluar (kalau mau pakai)
    const logoutBtn = document.getElementById("btn-logout");

    if (logoutBtn) {
        logoutBtn.addEventListener("click", function (e) {
            const ok = confirm("Yakin ingin keluar dari sistem?");
            if (!ok) {
                e.preventDefault();
            }
        });
    }
        // ==========================
    // 3. FILTER MATA KULIAH BERDASARKAN SEMESTER
    // ==========================
    const selectSemester = document.getElementById("select-semester");
    const selectMk = document.getElementById("select-mk");

    if (selectSemester && selectMk) {
        // simpan semua option asli
        const allOptions = Array.from(selectMk.options);

        function applyMkFilter() {
            const sem = selectSemester.value; // "2", "3", dst

            // kosongkan select dulu
            selectMk.innerHTML = "";

            // selalu tambahkan placeholder pertama
            const placeholder = allOptions[0].cloneNode(true);
            selectMk.appendChild(placeholder);

            // tambahkan hanya mk dengan data-semester yang cocok
            allOptions.slice(1).forEach(opt => {
                const optSem = opt.getAttribute("data-semester");
                if (!sem || optSem === sem) {
                    // pakai cloneNode biar array allOptions tetap utuh
                    selectMk.appendChild(opt.cloneNode(true));
                }
            });

            // reset pilihan
            selectMk.value = "";
        }

        selectSemester.addEventListener("change", applyMkFilter);
        // jalan sekali saat halaman pertama dibuka
        applyMkFilter();
    }

});



