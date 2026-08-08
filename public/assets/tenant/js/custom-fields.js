
// Initialize - ensure fields is always an array

let fields = Array.isArray(fieldsData) ? fieldsData : (typeof fieldsData === 'string' ? JSON.parse(fieldsData) :
    []);

// Load saved fields on page load
document.addEventListener('DOMContentLoaded', function () {
    if (fields.length > 0) {
        renderPreview();
    }

    // Update field button click (inside modal)
    document.getElementById('updateFieldBtn').addEventListener('click', function () {
        const id = parseInt(document.getElementById('editFieldId').value);
        const label = document.getElementById('editFieldLabel').value.trim();
        const required = document.getElementById('editFieldRequired').value === '1';

        if (!label) {
            $('#edit-field-required').text(__field_name__).show().delay(2000).fadeOut();
            return;
        }

        const field = fields.find(f => f.id === id);
        if (field) {
            field.label = label;
            field.required = required;
            renderPreview();
            $('#editFieldModal').modal('hide');
            saveToDatabase();
        }
    });
});

// Add field on Enter key press
document.getElementById('fieldLabel').addEventListener('keypress', function (e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        document.getElementById('addFieldBtn').click();
    }
});

// Add field button click
document.getElementById('addFieldBtn').addEventListener('click', function () {
    const label = document.getElementById('fieldLabel').value.trim();
    const required = document.getElementById('fieldRequired').value === '1';

    // Validation
    if (!label) {
        $('#field-required').text(__field_name__).show().delay(2000).fadeOut();
        return;
    }

    // Create field object
    const field = {
        id: Date.now(),
        label: label,
        required: required
    };

    fields.push(field);
    renderPreview();
    resetForm();

    // Auto-save to database
    saveToDatabase();
});

// Render preview
function renderPreview() {
    const container = document.getElementById('fieldsContainer');
    const emptyState = document.getElementById('emptyState');
    const countBadge = document.getElementById('fieldCount');

    if (fields.length === 0) {
        container.innerHTML = '';
        emptyState.style.display = 'block';
        countBadge.textContent = '0';
        return;
    }

    emptyState.style.display = 'none';
    countBadge.textContent = fields.length;

    container.innerHTML = fields.map((field, index) => `
                <div class="field-item" draggable="true" data-index="${index}" style="cursor: grab;">
                    <div class="field-item-header">
                        <div>
                            <div class="field-label">${escapeHtml(field.label)}
                                <span class="badge badge-${field.required !== false ? 'danger' : 'secondary'} ml-2">${field.required !== false ? __required__ : __optional__}</span>
                            </div>
                        </div>
                        <div class="field-actions">
                            <button type="button" class="btn btn-sm btn-warning" onclick="editField(${field.id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteFieldWithConfirm(${field.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');

    // Add drag and drop event listeners
    addDragDropListeners();
}

// Save to database via AJAX
function saveToDatabase() {
    const token = document.querySelector('#saveForm input[name="_token"]').value;
    const url = document.getElementById('saveForm').getAttribute('action');

    $.ajax({
        url: url,
        type: 'POST',
        data: {
            _token: token,
            wp_id: wpId,
            fields_data: JSON.stringify(fields)
        },
        success: function () {
            $.notify({
                title: 'Success',
                message: __saved_successfully__,
                icon: 'fa fa-bell'
            }, {
                type: 'success',
                placement: { from: 'top', align: 'right' },
                showProgressbar: true,
                time: 1000,
                delay: 3000
            });
        }
    });
}

// Delete field (temporary removal for editing)
function deleteField(id) {
    fields = fields.filter(f => f.id !== id);
    renderPreview();
}

// Delete field with confirmation (permanent deletion)
function deleteFieldWithConfirm(id) {
    if (confirm('Are you sure you want to delete this field?')) {
        deleteField(id);
        saveToDatabase();
    }
}

// Edit field - opens modal
function editField(id) {
    const field = fields.find(f => f.id === id);
    if (field) {
        document.getElementById('editFieldId').value = field.id;
        document.getElementById('editFieldLabel').value = field.label;
        document.getElementById('editFieldRequired').value = field.required !== false ? '1' : '0';
        $('#editFieldModal').modal('show');
    }
}

// Reset form
function resetForm() {
    document.getElementById('addFieldForm').reset();
}

// Drag and drop variables
let draggedIndex = null;

// Add drag and drop event listeners
function addDragDropListeners() {
    const fieldItems = document.querySelectorAll('.field-item');

    fieldItems.forEach(item => {
        item.addEventListener('dragstart', handleDragStart);
        item.addEventListener('dragover', handleDragOver);
        item.addEventListener('drop', handleDrop);
        item.addEventListener('dragend', handleDragEnd);
        item.addEventListener('dragleave', handleDragLeave);
    });
}

function handleDragStart(e) {
    draggedIndex = parseInt(this.getAttribute('data-index'));
    this.style.opacity = '0.5';
    e.dataTransfer.effectAllowed = 'move';
}

function handleDragOver(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    this.style.borderTop = '3px solid #007bff';
}

function handleDragLeave(e) {
    this.style.borderTop = 'none';
}

function handleDrop(e) {
    e.preventDefault();
    e.stopPropagation();

    const droppedIndex = parseInt(this.getAttribute('data-index'));

    if (draggedIndex !== null && draggedIndex !== droppedIndex) {
        // Reorder fields array
        const draggedField = fields[draggedIndex];
        fields.splice(draggedIndex, 1);
        fields.splice(droppedIndex, 0, draggedField);

        // Re-render and save
        renderPreview();
        saveToDatabase();
    }

    this.style.borderTop = 'none';
}

function handleDragEnd(e) {
    this.style.opacity = '1';
    this.style.borderTop = 'none';
    draggedIndex = null;
}




// Escape HTML
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}
