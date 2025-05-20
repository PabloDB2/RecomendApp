// archivo practicamente idéntico al de pelicula_detalle

document.addEventListener('DOMContentLoaded', function () {
    if (document.querySelector('.poster-carousel')) {
        new Swiper('.poster-carousel', {
            slidesPerView: 2,
            spaceBetween: 10,
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            breakpoints: {
                640: {
                    slidesPerView: 3,
                    spaceBetween: 15,
                },
                768: {
                    slidesPerView: 4,
                    spaceBetween: 15,
                },
                1024: {
                    slidesPerView: 5,
                    spaceBetween: 20,
                },
            }
        });
    }
    const trailerModal = document.getElementById('trailerModal');
    if (trailerModal) {
        trailerModal.addEventListener('show.bs.modal', function () {
            const videoId = document.querySelector('.video-thumbnail')?.dataset.videoId;
            if (videoId) {
                const iframe = document.getElementById('trailerIframe');
                iframe.src = `https://www.youtube.com/embed/${videoId}?autoplay=1`;
            }
        });

        trailerModal.addEventListener('hidden.bs.modal', function () {
            const iframe = document.getElementById('trailerIframe');
            iframe.src = '';
        });
    }
    document.querySelectorAll('.video-thumbnail').forEach(thumbnail => {
        thumbnail.addEventListener('click', function () {
            const videoId = this.dataset.videoId;
            const trailerModal = new bootstrap.Modal(document.getElementById('trailerModal'));
            document.getElementById('trailerIframe').src = `https://www.youtube.com/embed/${videoId}?autoplay=1`;
            trailerModal.show();
        });
    });
    document.querySelectorAll('.poster-image').forEach(image => {
        image.addEventListener('click', function () {
            const fullImage = this.dataset.fullImage;
            const imageModal = new bootstrap.Modal(document.getElementById('imageModal'));
            document.getElementById('fullSizeImage').src = fullImage;
            imageModal.show();
        });
    });
    const btnFavorite = document.querySelector('.btn-favorite');
    if (btnFavorite) {
        btnFavorite.addEventListener('click', function () {
            const serieId = this.dataset.serieId;
            const categoria = this.dataset.categoria;

            fetch('../ajax/toggle_favorito.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `api_id=${serieId}&categoria=${categoria}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const icon = this.querySelector('i');
                        icon.classList.toggle('far');
                        icon.classList.toggle('fas');
                        this.classList.toggle('active');
                    } else {
                        alert(data.message || 'Error al procesar la solicitud');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al procesar la solicitud');
                });
        });
    }
    const btnVista = document.querySelector('.btn-vista');
    if (btnVista) {
        btnVista.addEventListener('click', function () {
            const serieId = this.dataset.serieId;
            const categoria = this.dataset.categoria;

            fetch('../ajax/toggle_visto.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `api_id=${serieId}&categoria=${categoria}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const icon = this.querySelector('i');
                        icon.classList.toggle('far');
                        icon.classList.toggle('fas');
                        this.classList.toggle('active');
                    } else {
                        alert(data.message || 'Error al procesar la solicitud');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al procesar la solicitud');
                });
        });
    }
    const btnGuardar = document.querySelector('.btn-guardar');
    if (btnGuardar) {
        btnGuardar.addEventListener('click', function () {
            const serieId = this.dataset.serieId;
            const categoria = this.dataset.categoria;

            fetch('../ajax/toggle_lista.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `api_id=${serieId}&categoria=${categoria}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const icon = this.querySelector('i');
                        icon.classList.toggle('far');
                        icon.classList.toggle('fas');
                        this.classList.toggle('active');
                    } else {
                        alert(data.message || 'Error al procesar la solicitud');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al procesar la solicitud');
                });
        });
    }
    const reviewForm = document.getElementById('reviewForm');
    if (reviewForm) {
        reviewForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('../ajax/guardar_resena.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert(data.message || 'Error al guardar la reseña');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al procesar la solicitud');
                });
        });
    }
    const editReviewBtn = document.querySelector('.edit-review-btn');
    if (editReviewBtn) {
        editReviewBtn.addEventListener('click', function () {
            document.getElementById('userReview').classList.add('d-none');
            document.getElementById('editReviewForm').classList.remove('d-none');
        });
    }
    const cancelEditBtn = document.querySelector('.cancel-edit-btn');
    if (cancelEditBtn) {
        cancelEditBtn.addEventListener('click', function () {
            document.getElementById('userReview').classList.remove('d-none');
            document.getElementById('editReviewForm').classList.add('d-none');
        });
    }
    const editReviewForm = document.getElementById('editReviewForm');
    if (editReviewForm) {
        editReviewForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('../ajax/guardar_resena.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert(data.message || 'Error al actualizar la reseña');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al procesar la solicitud');
                });
        });
    }
    const deleteReviewBtn = document.querySelector('.delete-review-btn');
    if (deleteReviewBtn) {
        deleteReviewBtn.addEventListener('click', function () {
            if (confirm('¿Estás seguro de que deseas eliminar esta reseña?')) {
                const id = this.dataset.id;

                fetch('../ajax/eliminar_resena.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `id_reseña=${id}`
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.reload();
                        } else {
                            alert(data.message || 'Error al eliminar la reseña');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error al procesar la solicitud');
                    });
            }
        });
    }

    // Handle like review buttons
    document.querySelectorAll('.btn-like-review').forEach(button => {
        button.addEventListener('click', function () {
            const id = this.dataset.id;

            fetch('../ajax/toggle_like.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `id_resena=${id}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const icon = this.querySelector('i');
                        const likesCount = this.querySelector('.likes-count');

                        icon.classList.toggle('far');
                        icon.classList.toggle('fas');
                        this.classList.toggle('active');
                        likesCount.textContent = data.likes;
                    } else {
                        alert(data.message || 'Error al procesar la solicitud');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al procesar la solicitud');
                });
        });
    });
    const sortReviews = document.getElementById('sortReviews');
    if (sortReviews) {
        sortReviews.addEventListener('change', function () {
            const serieId = new URLSearchParams(window.location.search).get('id');
            const sort = this.value;

            fetch(`../ajax/reviews_ordenadas.php?id=${serieId}&sort=${sort}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.querySelector('.reviews-list').innerHTML = data.html;
                        document.querySelectorAll('.btn-like-review').forEach(button => {
                            button.addEventListener('click', function () {
                                const id = this.dataset.id;

                                fetch('../ajax/toggle_like.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/x-www-form-urlencoded'
                                    },
                                    body: `id_resena=${id}`
                                })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.success) {
                                            const icon = this.querySelector('i');
                                            const likesCount = this.querySelector('.likes-count');

                                            icon.classList.toggle('far');
                                            icon.classList.toggle('fas');
                                            this.classList.toggle('active');
                                            likesCount.textContent = data.likes;
                                        } else {
                                            alert(data.message || 'Error al procesar la solicitud');
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Error:', error);
                                        alert('Error al procesar la solicitud');
                                    });
                            });
                        });
                    } else {
                        alert('Error al cargar las reseñas');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al procesar la solicitud');
                });
        });
    }
});