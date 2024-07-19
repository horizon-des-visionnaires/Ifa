var modal = document.getElementById("imageModal");
    var modalImg = document.getElementById("img01");
    var captionText = document.getElementById("caption");

    document.querySelectorAll('.icon').forEach(function(img) {
        img.onclick = function() {
            modal.style.display = "block";
            modalImg.src = this.src;
            captionText.innerHTML = this.alt;
        }
    });

    var span = document.getElementsByClassName("close-image-modal")[0];

    span.onclick = function() {
        modal.style.display = "none";
    }