document.getElementById('sortBy').addEventListener('change', function() {
    const orderSelect = document.getElementById('order');
    const sortByValue = this.value;

    if (sortByValue) {
        orderSelect.options[0].text = `Les ${sortByValue === 'date' ? 'plus récents' : 'plus populaires'} en premier`;
        orderSelect.options[1].text = `Les ${sortByValue === 'date' ? 'plus anciens' : 'moins populaires'} en premier`;
    } else {
        orderSelect.options[0].text = 'Les plus récents en premier';
        orderSelect.options[1].text = 'Les plus anciens en premier';
    }
});