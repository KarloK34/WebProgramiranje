document.addEventListener('DOMContentLoaded', () => {
    const galleryContainer = document.getElementById('gallery-container-main');
    const loadApiImagesBtn = document.getElementById('load-api-images-btn');
    const loadLocalImagesBtn = document.getElementById('load-local-images-btn');

    async function displayImages(images) {
        if (!images || images.length === 0) {
            const p = document.createElement('p');
            p.textContent = 'Nema dostupnih slika za prikaz.';
            galleryContainer.appendChild(p);
            return;
        }

        images.forEach(image => {
            const figure = document.createElement('figure');
            figure.classList.add('galerija_slika');
            figure.dataset.imageId = image.id;

            const imgLink = document.createElement('a');
            imgLink.href = `#img${image.id}`;
            imgLink.onclick = () => showLightbox(image.putanja, image.opis || `Slika ${image.id}`);
            const imgElement = document.createElement('img');
            imgElement.src = image.putanja;
            imgElement.alt = image.opis || `Slika ${image.id}`;
            imgElement.loading = 'lazy';

            imgLink.appendChild(imgElement);
            figure.appendChild(imgLink);

            const figcaption = document.createElement('figcaption');
            figcaption.textContent = image.opis || `Slika ${image.id}`;
            if (image.izvor === 'api') {
                figcaption.textContent += " (API)";
            }
            figure.appendChild(figcaption);

            const ratingDiv = document.createElement('div');
            ratingDiv.classList.add('rating-stars');
            ratingDiv.dataset.imageId = image.id;
            for (let i = 1; i <= 5; i++) {
                const starSpan = document.createElement('span');
                starSpan.classList.add('star');
                starSpan.dataset.value = i;
                starSpan.innerHTML = '&#9733;';
                if (image.user_rating && i <= image.user_rating) {
                    starSpan.classList.add('rated');
                }
                starSpan.addEventListener('click', handleRating);
                starSpan.addEventListener('mouseover', (e) => highlightStars(e.target.parentElement, i, true, image.user_rating || 0));
                starSpan.addEventListener('mouseout', (e) => highlightStars(e.target.parentElement, image.user_rating || 0, false, image.user_rating || 0));
                ratingDiv.appendChild(starSpan);
            }
            figure.appendChild(ratingDiv);

            const avgRatingDiv = document.createElement('div');
            avgRatingDiv.classList.add('average-rating');
            avgRatingDiv.id = `avg-rating-${image.id}`;
            avgRatingDiv.textContent = image.average_rating ? `Prosjek: ${parseFloat(image.average_rating).toFixed(1)}/5 (${image.total_ratings} ocjena)` : 'Nema ocjena';
            figure.appendChild(avgRatingDiv);
            
            galleryContainer.appendChild(figure);
        });
    }

    async function fetchImages(sourceAction) {
        galleryContainer.innerHTML = '<p>Učitavanje slika...</p>';
        try {
            const response = await fetch(`gallery_handler.php?action=${sourceAction}`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const images = await response.json();
            galleryContainer.innerHTML = '';
            
            if (images.success === false) {
                galleryContainer.innerHTML = `<p>Greška: ${images.message || 'Nepoznata greška.'}</p>`;
                return;
            }
            displayImages(images);

        } catch (error) {
            console.error('Greška pri dohvaćanju slika:', error);
            galleryContainer.innerHTML = `<p>Greška pri učitavanju slika: ${error.message}. Molimo pokušajte kasnije.</p>`;
        }
    }

    function highlightStars(starsContainer, ratingValue, isHovering, currentDbRating) {
        const stars = starsContainer.querySelectorAll('.star');
        stars.forEach(star => {
            const starValue = parseInt(star.dataset.value);
            if (isHovering) {
                star.style.color = starValue <= ratingValue ? '#f8d64e' : '#ccc';
            } else {
                star.style.color = starValue <= currentDbRating ? '#f8d64e' : '#ccc';
            }
        });
    }

    async function handleRating(event) {
        const star = event.target;
        const ratingContainer = star.parentElement;
        const imageId = ratingContainer.dataset.imageId;
        const ratingValue = star.dataset.value;

        try {
            const formData = new FormData();
            formData.append('action', 'rate_image');
            formData.append('id_slika', imageId);
            formData.append('ocjena', ratingValue);

            const response = await fetch('gallery_handler.php', {
                method: 'POST',
                body: formData
            });
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({ message: 'Server error without JSON response' }));
                throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
            }
            const result = await response.json();

            if (result.success) {
                const currentStars = ratingContainer.querySelectorAll('.star');
                let newCurrentRating = 0;
                currentStars.forEach(s => {
                    const sValue = parseInt(s.dataset.value);
                    if (sValue <= ratingValue) {
                        s.classList.add('rated');
                        s.style.color = '#f8d64e';
                        newCurrentRating = ratingValue;
                    } else {
                        s.classList.remove('rated');
                        s.style.color = '#ccc';
                    }
                });

                const avgRatingDiv = document.getElementById(`avg-rating-${imageId}`);
                if (avgRatingDiv) {
                    avgRatingDiv.textContent = result.average_rating ? `Prosjek: ${parseFloat(result.average_rating).toFixed(1)}/5 (${result.total_ratings} ocjena)` : 'Nema ocjena';
                }

                if (galleryContainer.dataset.currentSource === 'api') {
                    fetchImages('fetch_api_images');
                } else {
                    fetchImages('get_images_with_ratings');
                }

            } else {
                alert(`Greška pri ocjenjivanju: ${result.message}`);
            }
        } catch (error) {
            console.error('Greška pri slanju ocjene:', error);
            alert(`Došlo je do greške: ${error.message}`);
        }
    }

    window.showLightbox = (src, caption) => {
        document.getElementById('lightbox-image-src').src = src;
        document.getElementById('lightbox-caption').textContent = caption;
        document.getElementById('image-lightbox').style.display = 'block';
    };
    window.closeLightbox = () => {
        document.getElementById('image-lightbox').style.display = 'none';
    };

    if (loadApiImagesBtn) {
        loadApiImagesBtn.addEventListener('click', () => {
            galleryContainer.dataset.currentSource = 'api';
            fetchImages('fetch_api_images');
        });
    }
    if (loadLocalImagesBtn) {
        loadLocalImagesBtn.addEventListener('click', () => {
            galleryContainer.dataset.currentSource = 'local';
            fetchImages('get_images_with_ratings');
        });
    }

    galleryContainer.dataset.currentSource = 'local';
    fetchImages('get_images_with_ratings');
});