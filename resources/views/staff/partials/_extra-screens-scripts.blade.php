<script>
    (function () {
        window.initStaffExtraScreens = function (options) {
            const accessToggle = options.accessToggle;
            const accessFields = options.accessFields;
            const categorySelect = options.categorySelect;
            const previewBox = options.previewBox;
            const extraCheckboxes = document.querySelectorAll('.extra-link-checkbox');
            const categoryLinksUrl = options.categoryLinksUrl;

            function updateExtraScreenTile(checkbox) {
                const tile = checkbox.closest('.extra-screen-tile');
                if (!tile) return;
                tile.classList.toggle('is-selected', checkbox.checked && !checkbox.disabled);
            }

            function updateExtraScreensCount() {
                const countEl = document.getElementById('extra-screens-count');
                if (!countEl) return;

                const count = Array.from(extraCheckboxes).filter(function (checkbox) {
                    return checkbox.checked && !checkbox.disabled;
                }).length;

                countEl.textContent = count + ' selected';
            }

            function syncExtraCheckboxes(inheritedLinkIds) {
                extraCheckboxes.forEach(function (checkbox) {
                    const tile = checkbox.closest('.extra-screen-tile');
                    const isInherited = inheritedLinkIds.includes(parseInt(checkbox.value, 10));

                    checkbox.disabled = isInherited;

                    if (tile) {
                        tile.classList.toggle('is-inherited', isInherited);
                    }

                    if (isInherited) {
                        checkbox.checked = false;
                    }

                    updateExtraScreenTile(checkbox);
                });

                updateExtraScreensCount();
            }

            function renderInheritedScreens(links) {
                if (!previewBox) return;

                if (!links.length) {
                    previewBox.innerHTML = '<span class="text-sm text-secondary-light">This category has no screens assigned yet.</span>';
                    return;
                }

                previewBox.innerHTML = links.map(function (link) {
                    return '<span class="screen-badge">' + link.link_name + '</span>';
                }).join('');
            }

            function loadCategoryScreens(categoryId) {
                if (!previewBox || (accessToggle && !accessToggle.checked)) return;

                if (!categoryId) {
                    previewBox.innerHTML = '<span class="text-sm text-secondary-light">Select a user category to preview inherited screens.</span>';
                    syncExtraCheckboxes([]);
                    return;
                }

                fetch(categoryLinksUrl + '/' + categoryId)
                    .then(function (response) { return response.json(); })
                    .then(function (data) {
                        const links = data.links || [];
                        const inheritedLinkIds = links.map(function (link) { return parseInt(link.link_id, 10); });
                        renderInheritedScreens(links);
                        syncExtraCheckboxes(inheritedLinkIds);
                    })
                    .catch(function () {
                        previewBox.innerHTML = '<span class="text-sm text-danger-600">Could not load category screens.</span>';
                    });
            }

            function toggleSystemAccess() {
                if (!accessToggle || !accessFields) return;

                const enabled = accessToggle.checked;
                accessFields.classList.toggle('is-disabled', !enabled);

                if (!enabled) {
                    if (categorySelect) categorySelect.value = '';
                    extraCheckboxes.forEach(function (checkbox) {
                        checkbox.checked = false;
                        updateExtraScreenTile(checkbox);
                    });
                    updateExtraScreensCount();

                    if (previewBox) {
                        previewBox.innerHTML = '<span class="text-sm text-secondary-light">System login is disabled for this staff member.</span>';
                    }
                } else if (categorySelect && categorySelect.value) {
                    loadCategoryScreens(categorySelect.value);
                }
            }

            extraCheckboxes.forEach(function (checkbox) {
                checkbox.addEventListener('change', function () {
                    updateExtraScreenTile(this);
                    updateExtraScreensCount();
                });
                updateExtraScreenTile(checkbox);
            });

            updateExtraScreensCount();

            if (accessToggle) {
                accessToggle.addEventListener('change', toggleSystemAccess);
                toggleSystemAccess();
            }

            if (categorySelect) {
                categorySelect.addEventListener('change', function () {
                    loadCategoryScreens(this.value);
                });

                if (accessToggle && accessToggle.checked && categorySelect.value) {
                    loadCategoryScreens(categorySelect.value);
                }
            }
        };
    })();
</script>
