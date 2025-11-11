{{-- TANDA TANGAN --}}
<table class="w-full border border-black text-sm mt-2 text-center">
    <tr>
        <td class="border border-black p-1 font-semibold">Effectiveness Verification</td>
        <td class="border border-black p-1 font-semibold">Status</td>
        <td class="border border-black p-1 font-semibold">Acknowledge</td>
        <td class="border border-black p-1 font-semibold">Approve</td>
    </tr>
    <tr>
        <td class="border border-black">
            <textarea name="effectiveness_verification" id="effectiveness_verification" class="w-full"></textarea>
        </td>
        <td class="border border-black p-2">
            {{-- STATUS --}}
            <div class="flex justify-between mt-3">
                <div class="text-lg font-bold">
                    Status:
                    <span
                        :class="{
                            'text-red-500': form.status_id == 6, // For status 6 (Open)
                            'text-green-600': form.status_id == 10, // For status 10 (Close)
                            'text-yellow-500': form.status_id != 6 && form.status_id !=
                                10 // For other statuses
                        }">
                        <span
                            x-text="form.status_id == 6 ? 'OPEN' : (form.status_id == 10 ? 'CLOSE' : (form.status_id == 7 ? 'SUBMITTED' : 'CHECKED BY DEPT HEAD'))">
                        </span>
                    </span>
                </div>
            </div>
        </td>
        <td class="border border-black">
            <div class="my-4">
                <!-- Verifikasi Lead Auditor -->
                <button onclick="verifySignature('lead_auditor_signature')">Verify</button>
                <template x-if="form.lead_auditor_signature">
                    <img :src="'/images/' + form.lead_auditor_signature" class="mx-auto mt-1 h-16 object-contain">
                </template>
            </div>

            <table class="w-full h-1/4">
                <tr>
                    <td class="border border-black">Lead Auditor</td>
                </tr>
                <tr>
                    <td>
                        <input type="text" name="" id="">
                    </td>
                </tr>
            </table>
        </td>
        <td class="border border-black">
            <div class="my-4">
                <!-- Verifikasi Auditor -->
                <button onclick="verifySignature('auditor_signature')">Verify</button>
                <template x-if="form.auditor_signature">
                    <img :src="'/images/' + form.auditor_signature" class="mx-auto mt-1 h-16 object-contain">
                </template>
            </div>

            <table class="w-full h-1/4">
                <tr>
                    <td class="border border-black">Auditor</td>
                </tr>
                <tr>
                    <td>
                        <input type="text" name="" id="">
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<script>
    // Simulasi data awal, bisa diganti dengan data dari database
    const form = {
        status_id: 8,  // Initial status: checked by auditor (id=8)
        auditor_signature: '',  // Gambar auditor signature (misalnya: 'auditor_signature.png')
        lead_auditor_signature: '',  // Gambar lead auditor signature (misalnya: 'lead_auditor_signature.png')
    };

    // Fungsi untuk memperbarui status
    // function updateStatus() {
    //     const statusElement = document.getElementById("status-text");

    //     if (form.status_id === 8) {
    //         statusElement.textContent = 'CHECKED BY DEPT HEAD';
    //         statusElement.classList.add("text-yellow-500");
    //         statusElement.classList.remove("text-red-500", "text-green-600");
    //     } else if (form.status_id === 10) {
    //         statusElement.textContent = 'CLOSE';
    //         statusElement.classList.add("text-green-600");
    //         statusElement.classList.remove("text-red-500", "text-yellow-500");
    //     } else {
    //         statusElement.textContent = 'OPEN';
    //         statusElement.classList.add("text-red-500");
    //         statusElement.classList.remove("text-green-600", "text-yellow-500");
    //     }
    // }

    // Verifikasi oleh Auditor
    function verifyAuditor() {
        // Hanya dapat mengubah status jika status saat ini adalah "CHECKED BY AUDITOR"
        if (form.status_id === 8) {
            // Status berubah menjadi "APPROVED BY AUDITOR"
            form.status_id = 9;  // Ubah status menjadi approved by auditor (id=9)
            // updateStatus();
            alert("Status updated to Approved by Auditor");

            // Simulasi tanda tangan auditor
            form.auditor_signature = 'auditor_signature.png';  // Ganti dengan nama file yang sesuai
            const imgElement = document.getElementById("auditor-signature");
            imgElement.src = '/images/' + form.auditor_signature;
            imgElement.style.display = 'block';  // Menampilkan gambar
        } else {
            alert("Status cannot be updated by Auditor at this stage.");
        }
    }

    // Verifikasi oleh Lead Auditor (hanya role admin)
    function verifyLeadAuditor() {
        // Cek apakah user yang login adalah lead auditor (role admin)
        const isAdmin = '{{ auth()->user()->role }}' === 'admin';  // Ganti dengan properti role yang sesuai

        if (form.status_id === 9 && isAdmin) {
            // Status berubah menjadi "CLOSE" (id=10)
            form.status_id = 10;
            // updateStatus();
            alert("Status updated to Closed");

            // Simulasi tanda tangan lead auditor (admin)
            form.lead_auditor_signature = 'lead_auditor_signature.png';  // Ganti dengan nama file yang sesuai
            const imgElement = document.getElementById("lead-auditor-signature");
            imgElement.src = '/images/' + form.lead_auditor_signature;
            imgElement.style.display = 'block';  // Menampilkan gambar
        } else if (!isAdmin) {
            alert("Only Lead Auditor (Admin) can perform this action.");
        } else {
            alert("Status cannot be updated by Lead Auditor at this stage.");
        }
    }

    // Inisialisasi status awal
    // updateStatus();
</script>
