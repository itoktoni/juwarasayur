{{-- Toast + seamless add-to-cart (include di halaman publik ecommerce) --}}
<div id="cart-toast" class="fixed bottom-5 left-1/2 -translate-x-1/2 z-[100] hidden">
    <div class="flex items-center gap-3 bg-on-surface text-surface px-5 py-3 rounded-full shadow-2xl">
        <span class="material-symbols-outlined text-green-400" id="cart-toast-icon">check_circle</span>
        <span class="text-sm font-medium whitespace-nowrap" id="cart-toast-msg"></span>
    </div>
</div>

<script>
    if (!window.addToCart) {
        let toastTimer;
        window.cartToast = function(message, isError = false) {
            const box = document.getElementById('cart-toast');
            if (!box) return;
            document.getElementById('cart-toast-msg').textContent = message;
            const icon = document.getElementById('cart-toast-icon');
            icon.textContent = isError ? 'error' : 'check_circle';
            icon.className = 'material-symbols-outlined ' + (isError ? 'text-red-400' : 'text-green-400');
            box.classList.remove('hidden');
            clearTimeout(toastTimer);
            toastTimer = setTimeout(() => box.classList.add('hidden'), 2500);
        };

        function bumpCartBadges(delta) {
            document.querySelectorAll('[data-cart-count]').forEach(el => {
                const next = Math.max(0, (parseInt(el.textContent || '0', 10) || 0) + delta);
                el.textContent = next;
                el.classList.toggle('hidden', next <= 0);
            });
        }

        window.addToCart = async function(productId, btn) {
            if (btn) { btn.disabled = true; btn.classList.add('opacity-60'); }
            try {
                const res = await fetch('{{ route("cart.add") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ product_id: productId, qty: 1 })
                });
                const json = await res.json();
                if (json.status) {
                    cartToast(json.message || 'Ditambahkan ke keranjang.');
                    bumpCartBadges(1);
                } else {
                    cartToast(json.message || 'Gagal menambahkan ke keranjang.', true);
                }
            } catch (e) {
                cartToast('Gagal menambahkan ke keranjang.', true);
            } finally {
                if (btn) { btn.disabled = false; btn.classList.remove('opacity-60'); }
            }
        };
    }
</script>
