document.addEventListener("DOMContentLoaded", () => {
  const addToCartBtn = document.getElementById('addToCartBtn');
  const shadeSelect = document.getElementById('shadeSelect');

  // récupérer ou initialiser le panier
  window.cart = JSON.parse(localStorage.getItem('cart')) || {};

  function saveCart() {
    localStorage.setItem('cart', JSON.stringify(window.cart));
    if (typeof window.renderCart === 'function') window.renderCart();
    if (typeof window.openCart === 'function') window.openCart();
  }

  addToCartBtn.addEventListener('click', () => {
    const name = addToCartBtn.dataset.name;
    let price = parseFloat(addToCartBtn.dataset.price);
    let image = addToCartBtn.dataset.image;
    let shade = null;

    if (shadeSelect && shadeSelect.value) {
      const selectedOption = shadeSelect.options[shadeSelect.selectedIndex];
      shade = selectedOption.value;
      price = parseFloat(selectedOption.dataset.price) || price;
      image = selectedOption.dataset.image || image;
    }

    const key = shade ? `${name} - ${shade}` : name;

    if (window.cart[key]) window.cart[key].quantity += 1;
    else window.cart[key] = { name, price, image_url: image, quantity: 1, shade };

    saveCart();
  });
});
