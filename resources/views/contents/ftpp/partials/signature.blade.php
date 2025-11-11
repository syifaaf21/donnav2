{{-- Signature Section --}}
<td class="border border-black p-2" x-data="signatureForm('dept_head')">
    <div class="text-center font-semibold mb-1">Dept. Head</div>
    <input type="file" accept="image/*" @change="uploadSignature($event)" class="text-xs mb-1">
    <template x-if="signatureUrl">
        <img :src="signatureUrl" class="mx-auto mt-1 h-16 object-contain border rounded">
    </template>

    <canvas x-ref="canvas" width="250" height="150" class="border"></canvas>
    <div class="flex justify-center gap-2 mt-1">
        <button type="button" class="px-2 py-1 bg-gray-300 text-xs rounded" @click="clearCanvas">Clear</button>
        <button type="button" class="px-2 py-1 bg-blue-600 text-white text-xs rounded" @click="saveCanvas">Save</button>
    </div>
    <input type="hidden" :name="role + '_signature'" :id="role + '_signature'" x-model="signatureData">
</td>

<td class="border border-black p-2" x-data="signatureForm('ldr_spv')">
    <div class="text-center font-semibold mb-1">Leader / Spv</div>
    <input type="file" accept="image/*" @change="uploadSignature($event)" class="text-xs mb-1">
    <template x-if="signatureUrl">
        <img :src="signatureUrl" class="mx-auto mt-1 h-16 object-contain border rounded">
    </template>

    <canvas x-ref="canvas" width="250" height="150" class="border"></canvas>
    <div class="flex justify-center gap-2 mt-1">
        <button type="button" class="px-2 py-1 bg-gray-300 text-xs rounded" @click="clearCanvas">Clear</button>
        <button type="button" class="px-2 py-1 bg-blue-600 text-white text-xs rounded" @click="saveCanvas">Save</button>
    </div>
    <input type="hidden" :name="role + '_signature'" :id="role + '_signature'" x-model="signatureData">
</td>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('signatureForm', (role) => ({
            role,
            signatureData: '',      // base64 hasil tanda tangan
            signatureUrl: '',       // preview
            drawing: false,
            ctx: null,

            init() {
                const canvas = this.$refs.canvas;
                this.ctx = canvas.getContext('2d');

                canvas.addEventListener('mousedown', () => this.drawing = true);
                canvas.addEventListener('mouseup', () => {
                    this.drawing = false;
                    this.ctx.beginPath();
                });
                canvas.addEventListener('mousemove', (e) => {
                    if (!this.drawing) return;
                    const rect = canvas.getBoundingClientRect();
                    this.ctx.lineWidth = 2;
                    this.ctx.lineCap = 'round';
                    this.ctx.strokeStyle = '#000';
                    this.ctx.lineTo(e.clientX - rect.left, e.clientY - rect.top);
                    this.ctx.stroke();
                    this.ctx.beginPath();
                    this.ctx.moveTo(e.clientX - rect.left, e.clientY - rect.top);
                });
            },

            clearCanvas() {
                const canvas = this.$refs.canvas;
                this.ctx.clearRect(0, 0, canvas.width, canvas.height);
                this.signatureUrl = '';
                this.signatureData = '';
            },

            saveCanvas() {
                const canvas = this.$refs.canvas;
                const blank = document.createElement('canvas');
                blank.width = canvas.width;
                blank.height = canvas.height;

                if (canvas.toDataURL() === blank.toDataURL()) {
                    alert('❌ Canvas kosong. Harap tanda tangan terlebih dahulu.');
                    return;
                }

                this.signatureData = canvas.toDataURL('image/png');
                this.signatureUrl = this.signatureData;
                alert(`${this.role.replace('_', ' ').toUpperCase()} signature saved!`);
            },

            uploadSignature(e) {
                const file = e.target.files[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = (evt) => {
                    this.signatureData = evt.target.result;
                    this.signatureUrl = evt.target.result;
                };
                reader.readAsDataURL(file);
            }
        }));
    });
</script>
@endpush
