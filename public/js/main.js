(function () {
    'use strict';

    var validationMessages = {
        invalidNameLength: 'Tên sản phẩm phải có từ 10 đến 100 ký tự.',
        invalidPrice: 'Giá phải là một số dương lớn hơn 0.'
    };

    var form = document.getElementById('product-form');
    if (!form) {
        return;
    }

    form.addEventListener('submit', function (event) {
        var nameInput = document.getElementById('name');
        var priceInput = document.getElementById('price');

        if (!nameInput || !priceInput) {
            return;
        }

        var nameLength = nameInput.value.trim().length;
        var priceValue = parseFloat(priceInput.value);
        var errors = [];

        if (nameLength < 10 || nameLength > 100) {
            errors.push(validationMessages.invalidNameLength);
        }

        if (Number.isNaN(priceValue) || priceValue <= 0) {
            errors.push(validationMessages.invalidPrice);
        }

        if (errors.length > 0) {
            event.preventDefault();
            window.alert(errors.join('\n'));
        }
    });
})();
