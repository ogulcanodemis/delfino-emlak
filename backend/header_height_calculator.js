// Header yükseklik hesaplama
// padding: 1rem = 16px
// Logo font-size: 1.8rem = yaklaşık 28px
// Line-height ve diğer elementler: ~20px
// Border-bottom: 2px
// Box-shadow ve backdrop ekstra alan: ~10px

const headerHeight = 16 + 16 + 28 + 20 + 2 + 10; // Total: ~92px
console.log('Hesaplanan header yüksekliği:', headerHeight, 'px');

// Gerçek hesaplama için JavaScript
function calculateHeaderHeight() {
    const header = document.querySelector('.header');
    if (header) {
        const height = header.offsetHeight;
        console.log('Gerçek header yüksekliği:', height, 'px');
        return height;
    }
    return 80; // Fallback
}

// CSS'te kullanılacak değer
const cssHeaderHeight = 90; // Güvenli margin ile
console.log('CSS için önerilen değer:', cssHeaderHeight, 'px');