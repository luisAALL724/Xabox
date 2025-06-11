let cart = [];
const cartItems = document.getElementById('cart-items');
const totalElement = document.getElementById('total');
const payButton = document.getElementById('pay-button');
const paypalButton = document.getElementById('paypal-button'); // Botón de PayPal
const mainImage = document.getElementById('main-image');

// Actualizar el carrito en pantalla
function updateCart() {
    cartItems.innerHTML = '';
    let total = 0;

    cart.forEach((item, index) => {
        total += item.price;

        // Crear elemento para el producto
        const listItem = document.createElement('li');
        listItem.textContent = `${item.name} - $${item.price} MXN`;

        // Crear botón de eliminar
        const removeButton = document.createElement('button');
        removeButton.textContent = 'Eliminar';
        removeButton.onclick = () => removeFromCart(index);

        listItem.appendChild(removeButton);
        cartItems.appendChild(listItem);
    });

    totalElement.textContent = `Total: $${total} MXN`;
    updatePayButton(total);
    updatePayPalButton(total); // Actualizar el botón de PayPal
}

// Eliminar producto del carrito
function removeFromCart(index) {
    cart.splice(index, 1);
    updateCart();
}

// Agregar producto al carrito
function addToCart(productName, price) {
    if (!productName || price <= 0) {
        console.error('Producto inválido o precio incorrecto.');
        return;
    }
    cart.push({ name: productName, price: price });
    updateCart();
}

// Enviar el carrito al servidor para Mercado Pago
function sendCartToServer() {
    fetch('ej.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(cart),
    })
        .then(response => response.json())
        .then(data => {
            console.log("Respuesta del servidor:", data);
            if (data.status === 'success') {
                window.location.href = `https://www.mercadopago.com.mx/checkout/v1/redirect?pref_id=${data.preference_id}`;
                

            } else {
                showErrorMessage('Error al crear la preferencia: ' + data.message);
            }
        })
        .catch(error => {
            console.error("Error en la solicitud:", error);
            showErrorMessage('Error al generar preferencia: ' + error.message);
        });
}

// Enviar el carrito al servidor para PayPal
function sendCartToPayPal() {
    fetch('payment.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(cart), // Enviar el carrito al servidor
    })
        .then(response => response.json())
        .then(data => {
            console.log("Respuesta del servidor (PayPal):", data);
            if (data.status === 'success' && data.redirect_url) {
                // Redirige al usuario a la URL proporcionada por PayPal
                window.location.href = `https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=EC-61743530YR322415D$%7Bdata.redirect_url%7D${data.redirect_url}`;
            } else {
                showErrorMessage('Error al crear preferencia para PayPal: ' + (data.message || 'Respuesta inválida del servidor.'));
            }
        })
        .catch(error => {
            console.error("Error en la solicitud (PayPal):", error);
            showErrorMessage('Error al generar preferencia para PayPal: ' + error.message);
        });
}


// Actualizar estado del botón de pago
function updatePayButton(total) {
    if (total > 0) {
        payButton.disabled = false;
        payButton.textContent = 'Pagar con Mercado Pago';
        payButton.onclick = sendCartToServer;
    } else {
        payButton.disabled = true;
        payButton.textContent = 'Carrito vacío';
        payButton.onclick = null;
    }
}

// Actualizar estado del botón de PayPal
function updatePayPalButton(total) {
    if (total > 0) {
        paypalButton.disabled = false; // Habilita el botón
        paypalButton.textContent = 'Pagar con PayPal';
        paypalButton.onclick = sendCartToPayPal; // Asocia la función al botón
    } else {
        paypalButton.disabled = true; // Deshabilita el botón si no hay productos
        paypalButton.textContent = 'Carrito vacío';
        paypalButton.onclick = null; // Elimina la acción si el botón está deshabilitado
    }
}


// Mostrar mensaje de error
function showErrorMessage(message) {
    console.error(message); // Muestra el error en la consola para depuración
    let errorContainer = document.getElementById('error-container');
    if (!errorContainer) {
        errorContainer = document.createElement('div');
        errorContainer.id = 'error-container';
        errorContainer.style.color = 'red';
        errorContainer.style.marginTop = '10px';
        document.body.appendChild(errorContainer);
    }
    errorContainer.textContent = message;

    setTimeout(() => {
        errorContainer.textContent = '';
    }, 5000);
}

// Cambiar imagen principal
function changeMainImage(imageSrc) {
    if (mainImage) {
        mainImage.src = imageSrc;
    }
}

// Inicializar botones
updatePayButton(0);
updatePayPalButton(0);
