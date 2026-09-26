

    const monthlyBtn =
        document.getElementById('monthlyBtn');

    const yearlyBtn =
        document.getElementById('yearlyBtn');

    const prices =
        document.querySelectorAll('.amount');


    monthlyBtn.addEventListener(
        'click',
        function () {

            monthlyBtn.classList.add('active');

            yearlyBtn.classList.remove('active');


            prices.forEach(price => {

                price.innerText =
                    price.dataset.monthly;

            });

        }
    );


    yearlyBtn.addEventListener(
        'click',
        function () {

            yearlyBtn.classList.add('active');

            monthlyBtn.classList.remove('active');


            prices.forEach(price => {

                price.innerText =
                    price.dataset.yearly;

            });

        }
    );

    // Company register //

     const avatarInput =
        document.getElementById('avatar');

    const avatarPreview =
        document.getElementById('avatarPreview');

    const avatarPlaceholder =
        document.getElementById('avatarPlaceholder');


    avatarInput.addEventListener(
        'change',
        function (event) {

            const file =
                event.target.files[0];


            if (!file) {

                avatarPreview.style.display = 'none';

                avatarPlaceholder.style.display = 'block';

                return;

            }


            const reader =
                new FileReader();


            reader.onload =
                function (e) {

                    avatarPreview.src =
                        e.target.result;

                    avatarPreview.style.display =
                        'block';

                    avatarPlaceholder.style.display =
                        'none';

                };


            reader.readAsDataURL(file);

        }
    );

