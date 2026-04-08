import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

const googleMapsApiKey = document
    .querySelector('meta[name="google-maps-api-key"]')
    ?.getAttribute('content')
    ?.trim();

const waitForGoogleMapsCore = (attempt = 0) => new Promise((resolve, reject) => {
    if (window.google?.maps?.Map) {
        resolve(window.google.maps);
        return;
    }

    if (attempt > 100) {
        reject(new Error('Google Maps failed to load.'));
        return;
    }

    window.setTimeout(() => {
        waitForGoogleMapsCore(attempt + 1).then(resolve).catch(reject);
    }, 150);
});

const waitForGoogleMaps = (attempt = 0) => new Promise((resolve, reject) => {
    if (window.google?.maps?.places?.PlaceAutocompleteElement) {
        resolve(window.google.maps);
        return;
    }

    if (attempt > 100) {
        reject(new Error('Google Maps Places failed to load.'));
        return;
    }

    window.setTimeout(() => {
        waitForGoogleMaps(attempt + 1).then(resolve).catch(reject);
    }, 150);
});

const extractAddressComponent = (components, type) =>
    components?.find((component) => component.types?.includes(type))?.longText ?? '';

const initPlaceAutocomplete = async () => {
    if (!googleMapsApiKey) {
        return;
    }

    const widgets = document.querySelectorAll('[data-google-place-widget]');

    if (!widgets.length) {
        return;
    }

    const maps = await waitForGoogleMaps();

    widgets.forEach((container) => {
        if (container.dataset.googlePlaceReady === 'true') {
            return;
        }

        const widget = new maps.places.PlaceAutocompleteElement({
            includedRegionCodes: ['au'],
            requestedLanguage: 'en',
        });

        widget.classList.add('google-place-widget');
        container.appendChild(widget);

        const handleSelection = async (event) => {
            const place =
                event.place ??
                event.placePrediction?.toPlace?.() ??
                event.detail?.placePrediction?.toPlace?.();

            if (!place) {
                return;
            }

            await place.fetchFields({
                fields: ['displayName', 'formattedAddress', 'location', 'addressComponents', 'id'],
            });

            const form = container.closest('form');
            const venueName = form?.querySelector('[data-event-venue-name]');
            const venueAddress = form?.querySelector('[data-event-venue-address]');
            const city = form?.querySelector('[data-event-city]');
            const latitude = form?.querySelector('[data-event-latitude]');
            const longitude = form?.querySelector('[data-event-longitude]');
            const placeId = form?.querySelector('[data-event-place-id]');

            if (venueName && !venueName.value.trim()) {
                venueName.value = place.displayName ?? '';
            }

            if (venueAddress) {
                venueAddress.value = place.formattedAddress ?? '';
            }

            if (city) {
                city.value =
                    extractAddressComponent(place.addressComponents, 'locality') ||
                    extractAddressComponent(place.addressComponents, 'postal_town') ||
                    extractAddressComponent(place.addressComponents, 'administrative_area_level_2');
            }

            if (latitude && place.location) {
                latitude.value = place.location.lat();
            }

            if (longitude && place.location) {
                longitude.value = place.location.lng();
            }

            if (placeId) {
                placeId.value = place.id ?? '';
            }
        };

        widget.addEventListener('gmp-select', handleSelection);
        widget.addEventListener('gmp-placeselect', handleSelection);

        container.dataset.googlePlaceReady = 'true';
    });
};

const initEventMaps = async () => {
    if (!googleMapsApiKey) {
        return;
    }

    const mapNodes = document.querySelectorAll('[data-event-map]');

    if (!mapNodes.length) {
        return;
    }

    const maps = await waitForGoogleMapsCore();

    mapNodes.forEach((node) => {
        if (node.dataset.mapReady === 'true') {
            return;
        }

        const latitude = Number.parseFloat(node.dataset.eventLatitude ?? '');
        const longitude = Number.parseFloat(node.dataset.eventLongitude ?? '');

        if (Number.isNaN(latitude) || Number.isNaN(longitude)) {
            return;
        }

        const position = { lat: latitude, lng: longitude };
        const map = new maps.Map(node, {
            center: position,
            zoom: 15,
            mapTypeControl: false,
            streetViewControl: false,
            fullscreenControl: false,
            styles: [
                { elementType: 'geometry', stylers: [{ color: '#1c1917' }] },
                { elementType: 'labels.text.fill', stylers: [{ color: '#d6d3d1' }] },
                { elementType: 'labels.text.stroke', stylers: [{ color: '#0c0a09' }] },
                { featureType: 'poi', stylers: [{ visibility: 'off' }] },
                { featureType: 'road', elementType: 'geometry', stylers: [{ color: '#44403c' }] },
                { featureType: 'water', elementType: 'geometry', stylers: [{ color: '#0f766e' }] },
            ],
        });

        new maps.Marker({
            position,
            map,
            title: node.dataset.eventTitle ?? 'Event location',
        });

        node.dataset.mapReady = 'true';
    });
};

initPlaceAutocomplete().catch((error) => {
    console.error(error);
});

initEventMaps().catch((error) => {
    console.error(error);
});
