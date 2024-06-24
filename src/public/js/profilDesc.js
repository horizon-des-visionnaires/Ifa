document.addEventListener("DOMContentLoaded", function() {
    const textarea = document.getElementById("inputFieldDesc");
    const charCount = document.getElementById("charCount");
    const maxLength = textarea.getAttribute("maxlength");

    textarea.addEventListener("input", function() {
        const currentLength = textarea.value.length;
        charCount.textContent = `${currentLength}/${maxLength}`;
    });
});