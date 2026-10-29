document.addEventListener('DOMContentLoaded', function () {
    const imageInput = document.getElementById('imagem_file');
    const previewContainer = document.getElementById('imagePreviewContainer');
    const previewImage = document.getElementById('imagePreview');
    const previewLabel = document.getElementById('imagePreviewLabel');

    if (imageInput && previewContainer && previewImage) {
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
    }

    const tagInputs = Array.from(document.querySelectorAll('.tag-input'));
    const suggestionButtons = document.querySelectorAll('.tag-suggestion');
    const suggestionHelp = document.getElementById('tagSuggestionHelp');

    suggestionButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const tag = (button.dataset.tag || '').trim();
            if (!tag) {
                return;
            }

            const existente = tagInputs.find(function (input) {
                return input.value.trim().toLowerCase() === tag.toLowerCase();
            });

            if (existente) {
                existente.focus();
                existente.select();
                return;
            }

            const vazio = tagInputs.find(function (input) {
                return input.value.trim() === '';
            });

            if (vazio) {
                vazio.value = tag;
                vazio.dispatchEvent(new Event('input', { bubbles: true }));
                vazio.focus();
                if (suggestionHelp) {
                    suggestionHelp.textContent = 'Clique para preencher a primeira tag vazia.';
                }
                return;
            }

            if (suggestionHelp) {
                suggestionHelp.textContent = 'As três tags já estão preenchidas. Remova uma para adicionar outra.';
            }
        });
    });
});
