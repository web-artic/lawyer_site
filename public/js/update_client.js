document.getElementById('editClientData1')?.addEventListener('click', function() {
    document.getElementById('editClientModal1').style.display = 'flex';
});

document.querySelector('.close')?.addEventListener('click', function() {
    document.getElementById('editClientModal1').style.display = 'none';
});

window.addEventListener('click', function(event) {
    if (event.target === document.getElementById('editClientModal1')) {
        document.getElementById('editClientModal1').style.display = 'none';
    }
});

window.addEventListener('load', function() {
    const hasErrors = document.getElementById('hasErrors').getAttribute('data-errors') === '1';
    if (hasErrors) {
        document.getElementById('editClientModal1').style.display = 'flex';
    }
});

function removeQueryParam(param) {
    const url = new URL(window.location);
    url.searchParams.delete(param); 
    window.history.replaceState({}, document.title, url); 
}

if (window.location.search.includes('success-info')) {
    setTimeout(() => {
        removeQueryParam('success-info'); 
    }, 5000); 
}
if (window.location.search.includes('success')) {
    setTimeout(() => {
        removeQueryParam('success'); 
    }, 5000); 
}
setTimeout(() => {
    document.querySelector('.success-message').style.display = 'none';
}, 5000);