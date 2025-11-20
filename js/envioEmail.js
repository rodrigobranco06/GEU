function openModal() {
    document.getElementById("uploadModal").classList.add("show");
}

function closeModal() {
    document.getElementById("uploadModal").classList.remove("show");
}

// fechar ao clicar fora do cartão
window.addEventListener("click", function (e) {
    const modal = document.getElementById("uploadModal");
    const card = document.querySelector(".upload-card");
    if (modal.classList.contains("show") && !card.contains(e.target) && e.target === modal) {
        closeModal();
    }
});

// quando escolher o ficheiro, mostra o nome no botão do formulário
function displayFileName() {
    const fileInput = document.getElementById("fileInput");
    const file = fileInput.files[0];
    if (file) {
        document.querySelector(".upload-btn").textContent = file.name;
        closeModal();
    }
}