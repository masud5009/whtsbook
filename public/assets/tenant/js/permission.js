(function () {
    function updatePermissionCount() {
        var totalChecked = document.querySelectorAll('input[name="permissions[]"]:checked').length;
        var alwaysOnOffset = 1;
        var countEl = document.getElementById('selectedPermissionCount');

        if (countEl) {
            countEl.textContent = Math.max(totalChecked - alwaysOnOffset, 0);
        }
    }

    function toggleChildPermissions(parentCheckbox) {
        var target = parentCheckbox.getAttribute('data-target');
        var wrapper = document.querySelector('.permission-children[data-parent="' + target + '"]');

        if (!wrapper) {
            return;
        }

        var enabled = parentCheckbox.checked;
        wrapper.style.opacity = enabled ? '1' : '0.55';

        wrapper.querySelectorAll('.permission-child-input').forEach(function (childCheckbox) {
            childCheckbox.disabled = !enabled;

            if (!enabled) {
                childCheckbox.checked = false;
            }
        });

        updatePermissionCount();
    }

    function initPermissionManagePage() {
        var page = document.querySelector('.permission-manage-page');
        if (!page) {
            return;
        }

        document.querySelectorAll('.permission-parent-trigger').forEach(function (parentCheckbox) {
            toggleChildPermissions(parentCheckbox);

            parentCheckbox.addEventListener('change', function () {
                toggleChildPermissions(parentCheckbox);
            });
        });

        document.querySelectorAll('.permission-child-input').forEach(function (childCheckbox) {
            childCheckbox.addEventListener('change', updatePermissionCount);
        });

        var searchInput = document.getElementById('permissionSearch');
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                var term = searchInput.value.trim().toLowerCase();

                document.querySelectorAll('.permission-module').forEach(function (moduleCard) {
                    var haystack = moduleCard.getAttribute('data-search') || '';
                    var shouldShow = !term || haystack.indexOf(term) !== -1;
                    moduleCard.style.display = shouldShow ? '' : 'none';
                });

                document.querySelectorAll('.permission-group-card').forEach(function (groupCard) {
                    var hiddenModules = groupCard.querySelectorAll('.permission-module[style="display: none;"]').length;
                    var totalModules = groupCard.querySelectorAll('.permission-module').length;
                    groupCard.style.display = hiddenModules === totalModules ? 'none' : '';
                });
            });
        }

        updatePermissionCount();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPermissionManagePage);
    } else {
        initPermissionManagePage();
    }
})();
