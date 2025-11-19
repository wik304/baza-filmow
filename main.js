document.addEventListener('DOMContentLoaded', function() {
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
        menuToggle.addEventListener('click', function() {
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
        editPhoneBtn.addEventListener('click', function() {
            displayPhoneGroup.style.display = 'none';
            editPhoneForm.style.display = 'block';
        });
        cancelPhoneBtn.addEventListener('click', function() {
            editPhoneForm.style.display = 'none';
            displayPhoneGroup.style.display = 'flex';
        });
    }

    const editEmailBtn = document.getElementById('edit-email-btn');
    const cancelEmailBtn = document.getElementById('cancel-email-btn');
    const displayEmailGroup = document.getElementById('email-display-group');
    const editEmailForm = document.getElementById('email-edit-form');

    if (editEmailBtn && cancelEmailBtn && displayEmailGroup && editEmailForm) {
        editEmailBtn.addEventListener('click', function() {
            displayEmailGroup.style.display = 'none';
            editEmailForm.style.display = 'block';
        });
        cancelEmailBtn.addEventListener('click', function() {
            editEmailForm.style.display = 'none';
            displayEmailGroup.style.display = 'flex';
        });
    }

    const searchInput = document.getElementById('search-input');
    const autocompleteResults = document.getElementById('autocomplete-results');

    if (searchInput && autocompleteResults) {
        let timeout = null;

        searchInput.addEventListener('input', function() {
            clearTimeout(timeout);
            const query = this.value.trim();

            if (query.length < 2) {
                autocompleteResults.innerHTML = '';
                autocompleteResults.style.display = 'none';
                return;
            }

            timeout = setTimeout(function() {
                fetch('autocomplete_movies.php?query=' + encodeURIComponent(query))
                    .then(response => response.json())
                    .then(data => {
                        autocompleteResults.innerHTML = '';
                        if (data.length > 0) {
                            data.forEach(movie => {
                                const resultItem = document.createElement('div');
                                resultItem.classList.add('autocomplete-item');
                                resultItem.innerHTML = `
                                    <span class="result-title">${movie.title}</span>
                                    <span class="result-year">${movie.release_year}</span>
                                `;
                                resultItem.addEventListener('click', function() {
                                    window.location.href = `movie.php?id=${movie.id}`;
                                });
                                autocompleteResults.appendChild(resultItem);
                            });
                            autocompleteResults.style.display = 'block';
                        } else {
                            autocompleteResults.style.display = 'none';
                        }
                    })
                    .catch(error => {
                        console.error('Błąd podczas pobierania danych autocomplete:', error);
                        autocompleteResults.style.display = 'none';
                    });
            }, 300);
        });

        document.addEventListener('click', function(event) {
            if (!searchInput.contains(event.target) && !autocompleteResults.contains(event.target)) {
                autocompleteResults.style.display = 'none';
            }
        });
    }
});
