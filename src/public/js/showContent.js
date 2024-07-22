document.addEventListener("DOMContentLoaded", function () {
    var profilPost = document.querySelector('.profilPost');
    var profilFavorites = document.querySelector('.profilFavorites');
    var showPostsButton = document.getElementById('showPosts');
    var showFavoritesButton = document.getElementById('showFavorites');

    profilPost.style.display = 'block';
    profilFavorites.style.display = 'none';

    showPostsButton.addEventListener('click', function () {
        profilPost.style.display = 'block';
        profilFavorites.style.display = 'none';
    });

    showFavoritesButton.addEventListener('click', function () {
        profilPost.style.display = 'none';
        profilFavorites.style.display = 'block';
    });
});