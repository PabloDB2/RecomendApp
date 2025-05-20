document.addEventListener('DOMContentLoaded', function() {
    const avatarOptions = document.querySelectorAll('.avatar-option');
    const currentAvatar = document.getElementById('current-avatar');

    avatarOptions.forEach(option => {
        option.addEventListener('click', function() {
            const selectedAvatar = this.getAttribute('data-avatar');
            fetch('../ajax/update_avatar.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `avatar=${selectedAvatar}`
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.text().then(text => {
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            console.error("Failed to parse JSON:", text);
                            throw new Error('Invalid JSON response from server');
                        }
                    });
                })
                .then(data => {
                    if (data.success) {
                        currentAvatar.src = `../Images/avatars/${selectedAvatar}`;
                        var profileHero = document.querySelector('.profile-hero');
                        if (profileHero) {
                            profileHero.style.backgroundImage = `linear-gradient(to bottom, rgba(0, 0, 0, 0.8), var(--black-dark)), url('../Images/avatars/${selectedAvatar}')`;
                        }
                        var modalElement = document.getElementById('avatarModal');
                        var modal = bootstrap.Modal.getInstance(modalElement);
                        if (modal) modal.hide();
                    } else {
                        alert(data.message || 'Error al actualizar el avatar');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al actualizar el avatar: ' + error.message);
                });
        });
    });
    
    function handleRemoveButtonClick() {
        const itemId = this.getAttribute('data-id');
        const itemTipo = this.getAttribute('data-tipo');
        const accion = this.getAttribute('data-accion');
        
        let apiEndpoint = '';
        switch (accion) {
            case 'favoritos':
                apiEndpoint = '../ajax/toggle_favorito.php';
                break;
            case 'vistos':
                apiEndpoint = '../ajax/toggle_visto.php';
                break;
            case 'lista':
                apiEndpoint = '../ajax/toggle_lista.php';
                break;
        }

        fetch(apiEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `api_id=${itemId}&categoria=${itemTipo}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const card = this.closest('.item-card, .movie-card');
                    card.style.opacity = '0';
                    setTimeout(() => {
                        card.remove();

                        const remainingItems = document.querySelectorAll('.item-card, .movie-card');
                        if (remainingItems.length === 0) {
                            location.reload(); 
                        }
                    }, 300);
                } else {
                    alert(data.message || 'Error al procesar la solicitud');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al procesar la solicitud');
            });
    }

    function setupEventHandlers() {
        document.querySelectorAll('.btn-action, .btn-remove, .btn-remove2').forEach(button => {
            button.addEventListener('click', handleRemoveButtonClick);
        });
    }
    
    setupEventHandlers();

    function cargarItems(categoria, seccion) {
        const contenedorItems = document.getElementById('itemsContainer');
        contenedorItems.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div></div>';
        fetch(`../ajax/perfil_items_ajax.php?categoria=${encodeURIComponent(categoria)}&seccion=${encodeURIComponent(seccion)}`)
            .then(response => response.text())
            .then(html => {
                contenedorItems.innerHTML = html;                setupEventHandlers();
            });
    }

    document.querySelectorAll('#categoryTabs .category-tab').forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            const categoria = this.getAttribute('data-categoria');
            const seccion = document.querySelector('#sectionTabs .section-tab.active').getAttribute('data-seccion');
            document.querySelectorAll('#categoryTabs .category-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            cargarItems(categoria, seccion);
            document.querySelectorAll('#sectionTabs .section-tab').forEach(stab => {
                stab.href = `?categoria=${categoria}&seccion=${stab.getAttribute('data-seccion')}`;
            });
        });
    });

    document.querySelectorAll('#sectionTabs .section-tab').forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            const seccion = this.getAttribute('data-seccion');
            const categoria = document.querySelector('#categoryTabs .category-tab.active').getAttribute('data-categoria');
            document.querySelectorAll('#sectionTabs .section-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            cargarItems(categoria, seccion);
        });
    });
});
