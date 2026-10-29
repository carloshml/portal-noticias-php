document.addEventListener('DOMContentLoaded', function () {
    const imageInput = document.getElementById('imagem_file');
    const previewContainer = document.getElementById('imagePreviewContainer');
    const previewImage = document.getElementById('imagePreview');
    const previewLabel = document.getElementById('imagePreviewLabel');

    if (!imageInput || !previewContainer || !previewImage) {
        return;
    }

    imageInput.addEventListener('change', function () {
        const file = this.files[0];

        if (!file) {
            return;
        }

        const reader = new FileReader();
        reader.addEventListener('load', function () {
            previewImage.src = this.result;
            previewContainer.style.display = 'block';
            if (previewLabel) {
                previewLabel.textContent = 'Nova imagem selecionada';
            }
        });
        reader.readAsDataURL(file);
    });
});
