document.addEventListener('DOMContentLoaded', function () {

    const announcementSlider = document.getElementById('announcement-slider');
    if (announcementSlider && typeof Splide !== 'undefined') {
        new Splide(announcementSlider, {
            type: 'loop',
            perPage: 1,
            arrows: false,
            pagination: true,
            autoplay: true,
            interval: 5000,
            pauseOnHover: true,
        }).mount();
    }

    const menuToggle = document.getElementById('mobile-menu-toggle');
    const navLinks = document.getElementById('nav-links-list');

    if (menuToggle && navLinks) {
        menuToggle.addEventListener('click', function () {
            navLinks.classList.toggle('nav-active');

            const icon = menuToggle.querySelector('i');
            if (navLinks.classList.contains('nav-active')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
                menuToggle.setAttribute('aria-expanded', 'true');
            } else {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
                menuToggle.setAttribute('aria-expanded', 'false');
            }
        });
    }

    const sliders = document.querySelectorAll('.movie-splide-slider');

    sliders.forEach(sliderElement => {
        if (typeof Splide !== 'undefined') {
            new Splide(sliderElement, {
                type: 'loop',
                perPage: 6,
                perMove: 1,
                gap: '1rem',
                autoplay: true,
                interval: 3000,
                pauseOnHover: true,
                pagination: false,
                arrows: true,
                breakpoints: {
                    1200: { perPage: 5 },
                    1000: { perPage: 4 },
                    800: { perPage: 3 },
                    600: { perPage: 2 },
                }
            }).mount();
        }
    });


    const editPhoneBtn = document.getElementById('edit-phone-btn');
    const cancelPhoneBtn = document.getElementById('cancel-phone-btn');
    const displayPhoneGroup = document.getElementById('phone-display-group');
    const editPhoneForm = document.getElementById('phone-edit-form');

    if (editPhoneBtn && cancelPhoneBtn && displayPhoneGroup && editPhoneForm) {
        editPhoneBtn.addEventListener('click', function () {
            displayPhoneGroup.style.display = 'none';
            editPhoneForm.style.display = 'block';
        });

        cancelPhoneBtn.addEventListener('click', function () {
            editPhoneForm.style.display = 'none';
            displayPhoneGroup.style.display = 'flex';
        });
    }

    const editEmailBtn = document.getElementById('edit-email-btn');
    const cancelEmailBtn = document.getElementById('cancel-email-btn');
    const displayEmailGroup = document.getElementById('email-display-group');
    const editEmailForm = document.getElementById('email-edit-form');

    if (editEmailBtn && cancelEmailBtn && displayEmailGroup && editEmailForm) {
        editEmailBtn.addEventListener('click', function () {
            displayEmailGroup.style.display = 'none';
            editEmailForm.style.display = 'block';
        });

        cancelEmailBtn.addEventListener('click', function () {
            editEmailForm.style.display = 'none';
            displayEmailGroup.style.display = 'flex';
        });
    }

});