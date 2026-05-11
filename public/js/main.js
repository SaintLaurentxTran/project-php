(function () {
    'use strict';

    var validationMessages = {
        invalidNameLength: 'Tên sản phẩm phải có từ 10 đến 100 ký tự.',
        invalidPrice: 'Giá phải lớn hơn hoặc bằng 0.01.'
    };

    var form = document.getElementById('product-form');
    if (form) {
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

            if (Number.isNaN(priceValue) || priceValue < 0.01) {
                errors.push(validationMessages.invalidPrice);
            }

            if (errors.length > 0) {
                event.preventDefault();
                window.alert(errors.join('\n'));
            }
        });
    }

    var sliders = document.querySelectorAll('[data-product-slider]');
    var sliderIntervals = [];
    if (sliders.length > 0) {
        sliders.forEach(function (slider) {
            var slides = slider.querySelectorAll('.slide-image');
            if (slides.length < 2) {
                return;
            }
            var activeIndex = 0;
            var intervalId = window.setInterval(function () {
                slides[activeIndex].classList.remove('is-active');
                activeIndex = (activeIndex + 1) % slides.length;
                slides[activeIndex].classList.add('is-active');
            }, 3000);
            sliderIntervals.push(intervalId);
        });
    }

    if (sliderIntervals.length > 0) {
        window.addEventListener('pagehide', function () {
            sliderIntervals.forEach(function (intervalId) {
                window.clearInterval(intervalId);
            });
        });
    }

})();
