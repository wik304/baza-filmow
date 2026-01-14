document.addEventListener('DOMContentLoaded', function () {
    const filterForm = document.getElementById('filter-form');
    if (filterForm) {
        const filterValueInput = document.getElementById('filter-value');
        const addFilterBtn = document.getElementById('add-filter-btn');
        const tagsContainer = document.getElementById('filter-tags-container');
        const hiddenFiltersContainer = document.getElementById('hidden-filters-container');

        function addFilterAndSubmit(value) {
            const trimmedValue = value.trim();
            if (!trimmedValue) return;

            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = `filters[]`;
            hiddenInput.value = trimmedValue;
            hiddenFiltersContainer.appendChild(hiddenInput);

            filterForm.submit();
        }

        function createTag(value) {
            const tag = document.createElement('div');
            tag.className = 'filter-tag';
            
            const span = document.createElement('span');
            span.className = 'tag-value';
            span.textContent = value;
            tag.appendChild(span);

            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'remove-tag-btn';
            btn.innerHTML = '&times;';
            tag.appendChild(btn);
            
            tagsContainer.appendChild(tag);

            btn.addEventListener('click', function () {
                const inputs = hiddenFiltersContainer.querySelectorAll('input[name="filters[]"]');
                for (const input of inputs) {
                    if (input.value === value) {
                        hiddenFiltersContainer.removeChild(input);
                        break;
                    }
                }
                filterForm.submit();
            });
        }

        addFilterBtn.addEventListener('click', function () {
            addFilterAndSubmit(filterValueInput.value);
        });

        filterValueInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addFilterAndSubmit(filterValueInput.value);
            }
        });

        if (window.appConfig && window.appConfig.currentFilters) {
            window.appConfig.currentFilters.forEach(value => {
                createTag(value);
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = `filters[]`;
                hiddenInput.value = value;
                hiddenFiltersContainer.appendChild(hiddenInput);
            });
        }
    }

    const announcementsTbody = document.getElementById('announcements-tbody');
    if (announcementsTbody) {
        if (window.innerWidth > 1000) {
            new Sortable(announcementsTbody, {
                animation: 150,
                onEnd: function (evt) {
                    const rows = announcementsTbody.querySelectorAll('tr');
                    const activeOrder = [];
                    let activeIndex = 1;

                    rows.forEach(row => {
                        if (!row.classList.contains('inactive-announcement')) {
                            activeOrder.push({
                                id: row.dataset.id,
                                order: activeIndex
                            });

                            const orderCell = row.querySelector('td[data-label="Kolejność"] span');
                            if (orderCell) {
                                orderCell.textContent = activeIndex;
                            }
                            activeIndex++;
                        }
                    });

                    fetch('update_announcement_order.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            order: activeOrder
                        }),
                    }).then(response => response.json()).then(data => {
                        if (data.success) console.log('Kolejność zaktualizowana.');
                    });
                }
            });
        }
    }

    const statusToggles = document.querySelectorAll('.status-toggle');
    statusToggles.forEach(toggle => {
        toggle.addEventListener('click', function (event) {
            const clickedOption = event.target.closest('.toggle-option');
            if (!clickedOption || clickedOption.classList.contains('active')) return;

            const announcementId = this.dataset.announcementId;
            const newStatus = clickedOption.dataset.status;
            const parentRow = this.closest('tr');
            const formData = new FormData();
            formData.append('id', announcementId);
            formData.append('is_active', newStatus);

            fetch('update_announcement_status.php', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.querySelectorAll('.toggle-option').forEach(opt => opt.classList.remove('active'));
                        clickedOption.classList.add('active');
                        const orderCell = parentRow.querySelector('td[data-label="Kolejność"]');
                        if (newStatus == 0) {
                            parentRow.classList.add('inactive-announcement');
                            if (orderCell) orderCell.textContent = '-';
                        } else {
                            parentRow.classList.remove('inactive-announcement');
                            if (orderCell && data.newOrder) orderCell.textContent = data.newOrder;
                        }
                    } else {
                        alert('Błąd aktualizacji.');
                    }
                }).catch(error => console.error('Error:', error));
        });
    });

    function handleUserChange(selectElement, endpoint, paramName, confirmMsgFunc) {
        selectElement.addEventListener('change', function () {
            const userId = this.dataset.userId;
            const newValue = this.value;
            const text = this.options[this.selectedIndex].text;

            if (!confirm(confirmMsgFunc(userId, text, newValue))) {
                this.value = this.querySelector('option[selected]').value;
                return;
            }
            const formData = new FormData();
            formData.append('user_id', userId);
            formData.append(paramName, newValue);

            fetch(endpoint, { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.querySelector('option[selected]').removeAttribute('selected');
                        this.querySelector(`option[value="${newValue}"]`).setAttribute('selected', 'selected');
                    } else {
                        alert('Błąd: ' + (data.message || 'Wystąpił błąd'));
                        window.location.reload();
                    }
                });
        });
    }

    const roleSelects = document.querySelectorAll('.user-role-select:not(.user-status-select)');
    roleSelects.forEach(select => {
        handleUserChange(select, 'update_user_role.php', 'new_role', (id, text) => `Czy zmienić rolę użytkownika ID ${id} na "${text}"?`);
    });

    const userStatusSelects = document.querySelectorAll('.user-status-select');
    userStatusSelects.forEach(select => {
        handleUserChange(select, 'update_user_status.php', 'is_banned', (id, text) => `Czy zmienić status użytkownika ID ${id} na "${text}"?`);
    });
});