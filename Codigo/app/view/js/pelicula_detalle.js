document.addEventListener("DOMContentLoaded", () => {
  //swiper para el scroll
  const posterSwiper = new Swiper(".poster-carousel", {
    slidesPerView: 1,
    spaceBetween: 10,
    navigation: {
      nextEl: ".swiper-button-next",
      prevEl: ".swiper-button-prev",
    },
    pagination: {
      el: ".swiper-pagination",
      clickable: true,
    },
    breakpoints: {
      576: {
        slidesPerView: 2,
        spaceBetween: 20,
      },
      768: {
        slidesPerView: 3,
        spaceBetween: 30,
      },
      992: {
        slidesPerView: 4,
        spaceBetween: 40,
      },
    },
  })

  const trailerModal = document.getElementById("trailerModal")
  const trailerIframe = document.getElementById("trailerIframe")

  if (trailerModal) {
    trailerModal.addEventListener("show.bs.modal", () => {
      const videos = document.querySelectorAll(".video-thumbnail")
      if (videos.length > 0) {
        const videoId = videos[0].getAttribute("data-video-id")
        trailerIframe.src = `https://www.youtube.com/embed/${videoId}?autoplay=1`
      }
    })

    trailerModal.addEventListener("hidden.bs.modal", () => {
      trailerIframe.src = ""
    })
  }

  // miniaturas para los videos (coge un frame del video)
  document.querySelectorAll(".video-thumbnail").forEach((thumbnail) => {
    thumbnail.addEventListener("click", function () {
      trailerIframe.src = `https://www.youtube.com/embed/${this.getAttribute("data-video-id")}?autoplay=1`
      new bootstrap.Modal(trailerModal).show()
    })
  })

  const imageModal = document.getElementById("imageModal")
  const fullSizeImage = document.getElementById("fullSizeImage")

  document.querySelectorAll(".poster-image").forEach((image) => {
    image.addEventListener("click", function () {
      fullSizeImage.src = this.getAttribute("data-full-image")
      new bootstrap.Modal(imageModal).show()
    })
  })

  // verifia login para las acciones que requieran tener cuenta
  function verificarLogin() {
    const userElement = document.querySelector(".reviews-container")
    if (!userElement || userElement.classList.contains("guest-user")) {
      alert("Debes iniciar sesión para usar esta función")
      return false
    }
    return true
  }

  // Botón de favoritos
  document.querySelectorAll(".btn-favorite").forEach((btnFavorito) => {
    btnFavorito.addEventListener("click", function (e) {
      e.preventDefault()
      if (!verificarLogin()) return

      const apiId = this.dataset.movieId || this.dataset.apiId
      const categoria = this.dataset.categoria
      const formData = new FormData()
      formData.append("api_id", apiId)
      formData.append("categoria", categoria)

      fetch("../ajax/toggle_favorito.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            this.classList.toggle("active", data.esFavorito)
            const icon = this.querySelector("i")
            if (icon) {
              icon.className = data.esFavorito ? "fas fa-heart" : "far fa-heart"
            }
          } else {
            alert(data.message)
          }
        })
        .catch((error) => {
          console.error("Error:", error)
        })
    })
  })

  // Botón de visto
  document.querySelectorAll(".btn-vista").forEach((btnVisto) => {
    btnVisto.addEventListener("click", function (e) {
      e.preventDefault()
      if (!verificarLogin()) return

      const apiId = this.dataset.movieId || this.dataset.apiId
      const categoria = this.dataset.categoria
      const formData = new FormData()
      formData.append("api_id", apiId)
      formData.append("categoria", categoria)

      fetch("../ajax/toggle_visto.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            this.classList.toggle("active", data.esVisto)
            const icon = this.querySelector("i")
            if (icon) {
              icon.className = data.esVisto ? "fas fa-eye" : "far fa-eye"
            }
          } else {
            alert(data.message)
          }
        })
        .catch((error) => {
          console.error("Error:", error)
        })
    })
  })

  // Botón de lista
  document.querySelectorAll(".btn-guardar").forEach((btnLista) => {
    btnLista.addEventListener("click", function (e) {
      e.preventDefault()
      if (!verificarLogin()) return

      const apiId = this.dataset.movieId || this.dataset.apiId
      const categoria = this.dataset.categoria
      const formData = new FormData()
      formData.append("api_id", apiId)
      formData.append("categoria", categoria)

      fetch("../ajax/toggle_lista.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            this.classList.toggle("active", data.enLista)
            const icon = this.querySelector("i")
            if (icon) {
              icon.className = data.enLista ? "fas fa-bookmark" : "far fa-bookmark"
            }
          } else {
            alert(data.message)
          }
        })
        .catch((error) => {
          console.error("Error:", error)
        })
    })
  })

  // RESEÑAS 
  //obtiene las referencias de los elementos del DOM
  const reviewForm = document.getElementById("reviewForm")
  const editReviewForm = document.getElementById("editReviewForm")
  const editReviewBtn = document.querySelector(".edit-review-btn")
  const cancelEditBtn = document.querySelector(".cancel-edit-btn")
  const deleteReviewBtn = document.querySelector(".delete-review-btn")
  const userReview = document.getElementById("userReview")

  // formulario de editar reseña
  if (editReviewBtn && editReviewForm && userReview) {
    editReviewBtn.addEventListener("click", () => {
      userReview.classList.add("d-none")
      editReviewForm.classList.remove("d-none")
    })
  }

  // cancelar edicion
  if (cancelEditBtn && editReviewForm && userReview) {
    cancelEditBtn.addEventListener("click", () => {
      editReviewForm.classList.add("d-none")
      userReview.classList.remove("d-none")
    })
  }

  // manejar errores (necesario)
  function fetchWithErrorHandling(url, options, onSuccess, onError) {
    const originalBtn = options.submitBtn
    const originalBtnText = originalBtn ? originalBtn.innerHTML : null

    if (originalBtn) {
      originalBtn.disabled = true
      originalBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...'
    }

    fetch(url, {
      method: options.method || "POST",
      body: options.body,
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error(`Error HTTP: ${response.status}`)
        }

        // parse de la respuesta a un formato JSON
        return response.text().then((text) => {
          try {
            return JSON.parse(text)
          } catch (e) {
           
            throw new Error("Error en la respuesta del servidor")
          }
        })
      })
      .then((data) => {
        if (data.success) {
          if (onSuccess) onSuccess(data)
        } else {
          if (onError) onError(new Error(data.message || "Error desconocido"))
        }
      })
      .catch((error) => {
        console.error("Error:", error)
        if (onError) onError(error)
      })
      .finally(() => {
        if (originalBtn) {
          originalBtn.disabled = false
          originalBtn.innerHTML = originalBtnText
        }
      })
  }

  // Crear reseña
  if (reviewForm) {
    reviewForm.addEventListener("submit", function (e) {
      e.preventDefault()

      const formData = new FormData(this)
      formData.append("accion", "crear")
      const submitBtn = this.querySelector('button[type="submit"]')

      fetchWithErrorHandling(
        "../ajax/guardar_resena.php",
        {
          method: "POST",
          body: formData,
          submitBtn: submitBtn,
        },
        (data) => {
          location.reload()
        },
        (error) => {
          alert("Error al enviar la reseña: " + error.message)
        },
      )
    })
  }

  // Guardar edicion
  if (editReviewForm) {
    editReviewForm.addEventListener("submit", function (e) {
      e.preventDefault()
      const formData = new FormData(this)
      const submitBtn = this.querySelector('button[type="submit"]')

      fetchWithErrorHandling(
        "../ajax/guardar_resena.php",
        {
          method: "POST",
          body: formData,
          submitBtn: submitBtn,
        },
        (data) => {
          location.reload()
        },
        (error) => {
          alert("Error al actualizar la reseña: " + error.message)
        },
      )
    })
  }

  // Eliminar reseña
  if (deleteReviewBtn) {
    deleteReviewBtn.addEventListener("click", function (e) {
      e.preventDefault()
      if (confirm("¿Estás seguro de que deseas eliminar tu reseña?")) {
        const id_resena = this.getAttribute("data-id")
        if (!id_resena) {
          console.error("Error: No se encontró el ID de la reseña")
          return
        }
        const formData = new FormData()
        formData.append("id_reseña", id_resena)
        
        const originalBtnText = this.innerHTML
        this.disabled = true
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Eliminando...'

        fetch("../ajax/eliminar_resena.php", {
          method: "POST",
          body: formData,
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.success) {
              location.reload()
            } else {
              alert(data.message || "Error al eliminar la reseña")
            }
          })
          .catch((error) => {
            console.error("Error:", error)
            alert("Error al eliminar la reseña")
          })
          .finally(() => {
            this.disabled = false
            this.innerHTML = originalBtnText
          })
      }
    })
  }

  function asignarEventosLikeReview() {
    document.querySelectorAll(".btn-like-review").forEach((btn) => {
      const newBtn = btn.cloneNode(true)
      btn.replaceWith(newBtn)
    })
    document.querySelectorAll(".btn-like-review").forEach((btn) => {
      btn.addEventListener("click", function (e) {
        e.preventDefault()
        if (!verificarLogin()) return
        const id_resena = this.getAttribute("data-id")
        if (!id_resena) return
        const formData = new FormData()
        formData.append("id_resena", id_resena)
        const icon = this.querySelector("i")
        const likesCount = this.querySelector(".likes-count")
        this.disabled = true
        fetch("../ajax/toggle_like.php", {
          method: "POST",
          body: formData,
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.success) {
              this.classList.toggle("active", data.liked)
              if (icon) icon.className = data.liked ? "fas fa-heart" : "far fa-heart"
              if (likesCount) likesCount.textContent = data.likes
            } else {
              alert(data.message || "Error al dar like a la reseña")
            }
          })
          .catch(() => alert("Error al dar like a la reseña."))
          .finally(() => {
            this.disabled = false
          })
      })
    })
  }
  asignarEventosLikeReview()

  // Botón de leer mas
  function asignarLeerMas() {
    // Elimina todos los botones previos de leer más
    document.querySelectorAll(".leer-mas-btn").forEach((btn) => btn.remove())
    document.querySelectorAll(".review-text").forEach((p) => {
      const lineHeight = Number.parseFloat(getComputedStyle(p).lineHeight)
      const maxLines = 6
      const maxHeight = lineHeight * maxLines
      p.style.maxHeight = ""
      p.style.overflow = ""
      p.style.position = ""
      p.style.wordBreak = "break-word"
      p.style.whiteSpace = "pre-line"
      p.style.width = "100%"
      p.style.display = "block"
      p.style.boxSizing = "border-box"
      p.style.marginBottom = "10px"
      p.style.maxWidth = "100%"
      p.style.minWidth = "0"
      if (p.scrollHeight > maxHeight + 1) {
        p.style.maxHeight = maxHeight + "px"
        p.style.overflow = "hidden"
        p.style.position = "relative"
        const btn = document.createElement("button")
        btn.className = "leer-mas-btn"
        btn.textContent = "Leer más"
        btn.addEventListener("click", () => {
          if (p.style.maxHeight !== "none") {
            p.style.maxHeight = "none"
            btn.textContent = "Leer menos"
          } else {
            p.style.maxHeight = maxHeight + "px"
            btn.textContent = "Leer más"
          }
        })
        p.after(btn)
      }
    })
  }
  asignarLeerMas()
  asignarEventosLikeReview()
  asignarLeerMas()
  setTimeout(() => {
    asignarEventosLikeReview()
    asignarLeerMas()
  }, 0)

  // Ordenar reseñas con ajax
  const sortReviewsSelect = document.getElementById("sortReviews")
  const sortReviewsForm = document.getElementById("sortReviewsForm")
  const reviewsList = document.querySelector(".reviews-list")
  if (sortReviewsSelect && sortReviewsForm && reviewsList) {
    sortReviewsSelect.addEventListener("change", (e) => {
      e.preventDefault()
      const movieId = sortReviewsForm.querySelector('input[name="id"]').value
      const sort = sortReviewsSelect.value
      reviewsList.innerHTML =
        '<div class="text-center py-4"><div class="spinner-border text-success" role="status"><span class="visually-hidden">Cargando...</span></div></div>'
      fetch(`../ajax/reviews_ordenadas.php?id=${encodeURIComponent(movieId)}&sort=${encodeURIComponent(sort)}`)
        .then((res) => res.json())
        .then((data) => {
          if (data.success) {
            reviewsList.innerHTML = data.html
            asignarEventosLikeReview()
            asignarLeerMas()
          } else {
            reviewsList.innerHTML = '<div class="text-center py-4">Error al cargar las reseñas.</div>'
          }
        })
        .catch(() => {
          reviewsList.innerHTML = '<div class="text-center py-4">Error al cargar las reseñas.</div>'
        })
    })
  }
})
