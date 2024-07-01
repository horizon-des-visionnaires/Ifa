function toggleVisibility(elementId, buttonId) {
    var element = document.getElementById(elementId);
    var button = document.getElementById(buttonId);

    if (element.style.display === "none") {
        element.style.display = "block";
        button.textContent = "Voir moins";
    } else {
        element.style.display = "none";
        button.textContent = "Voir plus";
    }
}
