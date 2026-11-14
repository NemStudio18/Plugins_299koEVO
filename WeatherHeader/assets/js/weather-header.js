/**
 * WeatherHeader.js — Contrôleur d'En-tête Météo Vectoriel Vivant
 * Appel direct Open-Meteo haute performance (0 dépendance externe).
 */
document.addEventListener("DOMContentLoaded", function () {
    var scene = document.getElementById("meteo_living_scene");
    var fallbackHost = document.getElementById("weather_header_fallback_host");

    // 1. Montage automatique pour les thèmes sans hook direct (ex: Ebene, Ignis, DesktopOS, etc.)
    if (!scene && fallbackHost) {
        var living = fallbackHost.querySelector("#meteo_living_scene");
        if (living) {
            var target = document.querySelector(".ebene-banner, .declic-banner, .ignis-banner, #banner, header#header, header.ebene-header, #header, header");
            if (target && target.parentNode) {
                target.parentNode.insertBefore(living, target);
                target.style.display = "none";
                scene = living;
            } else {
                document.body.insertBefore(living, document.body.firstChild);
                scene = living;
            }
        }
    }

    if (!scene) return;

    // 2. Masquage automatique des bannières natives du thème actif pour un remplacement propre
    var defaultBanners = document.querySelectorAll("#banner:not(#meteo_living_scene), .declic-banner:not(#meteo_living_scene), .ebene-banner:not(#meteo_living_scene), .ignis-banner:not(#meteo_living_scene)");
    defaultBanners.forEach(function (b) {
        b.style.display = "none";
    });

    var defaultCity = scene.getAttribute("data-city") || "Bourges";
    var lat = parseFloat(scene.getAttribute("data-lat")) || 47.0810;
    var lon = parseFloat(scene.getAttribute("data-lon")) || 2.3988;
    var autoGeo = scene.getAttribute("data-auto-geo") === "1";

    // Vérifier si le visiteur a des coordonnées déjà mémorisées
    var savedGeo = null;
    try {
        var stored = localStorage.getItem("wh_user_geo");
        if (stored) savedGeo = JSON.parse(stored);
    } catch (e) {}

    if (savedGeo && savedGeo.lat && savedGeo.lon) {
        lat = savedGeo.lat;
        lon = savedGeo.lon;
        defaultCity = savedGeo.city || defaultCity;
    }

    var tempEl = scene.querySelector('[data-role="ws-temp"]');
    var condEl = scene.querySelector('[data-role="ws-condition"]');
    var cityEl = scene.querySelector('[data-role="ws-city"]');
    var subEl = scene.querySelector('[data-role="ws-sub"]');
    var windEl = scene.querySelector('[data-role="ws-wind"]');
    var windArrowEl = scene.querySelector('[data-role="ws-wind-arrow"]');
    var humEl = scene.querySelector('[data-role="ws-humidity"]');
    var precipEl = scene.querySelector('[data-role="ws-precip"]');
    var pressEl = scene.querySelector('[data-role="ws-pressure"]');
    var pickerBtns = scene.querySelectorAll(".ws-pick-btn");
    var timeBtn = document.getElementById("ws_time_toggle");

    var currentDayNight = "day";
    var currentWeatherType = "sunny";
    var realState = {
        type: "sunny",
        isDay: true,
        temp: 16,
        cond: "Ensoleillé",
        city: defaultCity,
        windSpeed: 12,
        windDir: 220,
        humidity: 60,
        precip: 0,
        pressure: 1015
    };

    function getWindCardinal(deg) {
        var cardinals = ["N", "NNE", "NE", "ENE", "E", "ESE", "SE", "SSE", "S", "SSO", "SO", "OSO", "O", "ONO", "NO", "NNO"];
        var idx = Math.round(deg / 22.5) % 16;
        return cardinals[idx];
    }

    function applyScene(type, isDay) {
        currentWeatherType = type;
        currentDayNight = isDay ? "day" : "night";

        var allStates = [
            "weather-state-sunny",
            "weather-state-night",
            "weather-state-clear",
            "weather-state-cloudy",
            "weather-state-rain",
            "weather-state-storm",
            "weather-state-snow",
            "weather-state-fog"
        ];
        allStates.forEach(function (st) { scene.classList.remove(st); });
        scene.classList.remove("ws-time-day", "ws-time-night");

        scene.classList.add(isDay ? "ws-time-day" : "ws-time-night");

        if (type === "sunny") {
            scene.classList.add(isDay ? "weather-state-sunny" : "weather-state-night");
        } else {
            scene.classList.add("weather-state-" + type);
        }

        if (timeBtn) {
            timeBtn.innerHTML = isDay ? "🌙 Nuit" : "☀️ Jour";
            timeBtn.title = isDay ? "Passer en mode nuit" : "Passer en mode jour";
        }
    }

    function determineWeatherType(weatherCode) {
        if (weatherCode === 0 || weatherCode === 1) return "sunny";
        if (weatherCode === 2 || weatherCode === 3) return "cloudy";
        if (weatherCode >= 45 && weatherCode <= 48) return "fog";
        if ((weatherCode >= 51 && weatherCode <= 67) || (weatherCode >= 80 && weatherCode <= 82)) return "rain";
        if ((weatherCode >= 71 && weatherCode <= 77) || (weatherCode >= 85 && weatherCode <= 86)) return "snow";
        if (weatherCode >= 95 && weatherCode <= 99) return "storm";
        return "sunny";
    }

    function getWeatherDesc(weatherCode, isDay) {
        var map = {
            0: isDay ? "Ensoleillé" : "Nuit claire étoilée",
            1: isDay ? "Peu nuageux" : "Nuit dégagée",
            2: isDay ? "Partiellement nuageux" : "Nuit partiellement nuageuse",
            3: isDay ? "Ciel couvert" : "Nuit couverte",
            45: isDay ? "Brouillard" : "Brouillard de nuit",
            48: isDay ? "Brouillard givrant" : "Brume givrante nocturne",
            51: isDay ? "Bruine légère" : "Bruine de nuit",
            53: isDay ? "Bruine modérée" : "Pluie fine nocturne",
            55: isDay ? "Bruine dense" : "Pluie dense nocturne",
            61: isDay ? "Pluie faible" : "Pluie de nuit",
            63: isDay ? "Pluie modérée" : "Pluie de nuit battante",
            65: isDay ? "Forte pluie" : "Averse de nuit",
            71: isDay ? "Chute de neige" : "Neige de nuit",
            73: isDay ? "Chute de neige modérée" : "Neige nocturne",
            75: isDay ? "Forte chute de neige" : "Tempête de neige nocturne",
            80: isDay ? "Averses de pluie" : "Averses de nuit",
            81: isDay ? "Fortes averses" : "Fortes averses de nuit",
            82: isDay ? "Averses violentes" : "Pluie torrentielle de nuit",
            95: isDay ? "Orage d'été" : "Orage de nuit",
            96: isDay ? "Orage violent" : "Orage de nuit violent",
            99: isDay ? "Orage avec grêle" : "Orage de nuit avec grêle"
        };
        return map[weatherCode] || (isDay ? "Ciel variable" : "Nuit variable");
    }

    function fetchMeteo(targetLat, targetLon, cityName) {
        var url = "https://api.open-meteo.com/v1/forecast?latitude=" + targetLat + "&longitude=" + targetLon + "&current=temperature_2m,relative_humidity_2m,surface_pressure,weather_code,is_day,wind_speed_10m,wind_direction_10m,precipitation&hourly=precipitation_probability&forecast_days=1&timezone=auto";
        return fetch(url)
            .then(function (res) { return res.ok ? res.json() : null; })
            .then(function (payload) {
                if (!payload || !payload.current) {
                    fallbackDefault();
                    return;
                }
                var curr = payload.current;
                var isDay = (curr.is_day === 1);
                var code = parseInt(curr.weather_code, 10);
                var temp = Math.round(curr.temperature_2m);
                var windSpeed = Math.round(curr.wind_speed_10m || 0);
                var windDir = Math.round(curr.wind_direction_10m || 0);
                var humidity = Math.round(curr.relative_humidity_2m || 0);
                var pressure = Math.round(curr.surface_pressure || 1013);
                var precipProb = 0;
                if (payload.hourly && payload.hourly.precipitation_probability && payload.hourly.precipitation_probability.length > 0) {
                    var curHour = new Date().getHours();
                    precipProb = Math.round(payload.hourly.precipitation_probability[curHour] || payload.hourly.precipitation_probability[0] || 0);
                } else if (curr.precipitation && curr.precipitation > 0) {
                    precipProb = Math.min(100, Math.round(curr.precipitation * 30));
                }

                realState = {
                    type: determineWeatherType(code),
                    isDay: isDay,
                    temp: temp,
                    cond: getWeatherDesc(code, isDay),
                    city: cityName || defaultCity,
                    windSpeed: windSpeed,
                    windDir: windDir,
                    humidity: humidity,
                    pressure: pressure,
                    precip: precipProb
                };

                if (tempEl) tempEl.textContent = realState.temp;
                if (condEl) condEl.textContent = realState.cond;
                if (cityEl) cityEl.textContent = realState.city;
                if (subEl) subEl.textContent = isDay ? "Observations du jour" : "Observations nocturnes";

                if (windEl) {
                    windEl.textContent = windSpeed + " km/h " + getWindCardinal(windDir);
                }
                if (windArrowEl) {
                    windArrowEl.style.transform = "rotate(" + (windDir + 180) + "deg)";
                }
                if (humEl) humEl.textContent = humidity + "%";
                if (precipEl) precipEl.textContent = precipProb + "%";
                if (pressEl) pressEl.textContent = pressure + " hPa";

                applyScene(realState.type, realState.isDay);
            })
            .catch(function () {
                fallbackDefault();
            });
    }

    function fallbackDefault() {
        var hour = new Date().getHours();
        var isDay = hour >= 7 && hour < 20;
        realState = {
            type: "sunny",
            isDay: isDay,
            temp: isDay ? 16 : 11,
            cond: isDay ? "Ensoleillé" : "Nuit claire étoilée",
            city: defaultCity,
            windSpeed: 10,
            windDir: 210,
            humidity: 65,
            pressure: 1016,
            precip: 5
        };
        if (tempEl) tempEl.textContent = realState.temp;
        if (condEl) condEl.textContent = realState.cond;
        if (cityEl) cityEl.textContent = realState.city;
        if (subEl) subEl.textContent = isDay ? "Journée estimée" : "Nuit estimée";
        if (windEl) windEl.textContent = "10 km/h SO";
        if (humEl) humEl.textContent = "65%";
        if (precipEl) precipEl.textContent = "5%";
        if (pressEl) pressEl.textContent = "1016 hPa";
        applyScene(realState.type, realState.isDay);
    }

    function reverseGeocodeAndFetch(uLat, uLon) {
        function saveAndApply(cityName) {
            try {
                localStorage.setItem("wh_user_geo", JSON.stringify({ lat: uLat, lon: uLon, city: cityName }));
            } catch (e) {}
            if (cityEl) cityEl.textContent = cityName;
            fetchMeteo(uLat, uLon, cityName);
        }

        var geoApi = "https://api.bigdatacloud.net/data/reverse-geocode-client?latitude=" + uLat + "&longitude=" + uLon + "&localityLanguage=fr";
        fetch(geoApi)
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (data) {
                var detectedCity = null;
                if (data) {
                    detectedCity = data.city || data.locality || data.town || data.village;
                }
                if (detectedCity) {
                    saveAndApply(detectedCity);
                } else {
                    // Fallback Nominatim
                    var nomApi = "https://nominatim.openstreetmap.org/reverse?format=json&lat=" + uLat + "&lon=" + uLon + "&accept-language=fr";
                    fetch(nomApi)
                        .then(function (nr) { return nr.ok ? nr.json() : null; })
                        .then(function (ndata) {
                            var nCity = defaultCity;
                            if (ndata && ndata.address) {
                                var a = ndata.address;
                                nCity = a.city || a.town || a.village || a.municipality || defaultCity;
                            }
                            saveAndApply(nCity);
                        })
                        .catch(function () {
                            saveAndApply(defaultCity);
                        });
                }
            })
            .catch(function () {
                saveAndApply(defaultCity);
            });
    }

    // 3. Chargement initial immédiat (sans bloquer l'affichage)
    fetchMeteo(lat, lon, defaultCity);

    // 4. Si le plugin météo est absent (autoGeo=true), demander automatiquement la géolocalisation
    if (autoGeo && navigator.geolocation) {
        var geoRefused = false;
        try {
            geoRefused = (localStorage.getItem("wh_geo_refused") === "1");
        } catch (e) {}

        // Si l'utilisateur n'a pas déjà refusé et n'a pas encore de position mémorisée
        if (!geoRefused && (!savedGeo || !savedGeo.lat)) {
            navigator.geolocation.getCurrentPosition(
                function (pos) {
                    var uLat = pos.coords.latitude;
                    var uLon = pos.coords.longitude;
                    reverseGeocodeAndFetch(uLat, uLon);
                },
                function (err) {
                    try {
                        localStorage.setItem("wh_geo_refused", "1");
                    } catch (e) {}
                },
                { timeout: 8000, maximumAge: 3600000 }
            );
        }
    }

    // 5. Panneau de contrôle interactif (Admin uniquement)
    if (pickerBtns.length > 0) {
        pickerBtns.forEach(function (btn) {
            btn.addEventListener("click", function () {
                pickerBtns.forEach(function (b) { b.classList.remove("is-active"); });
                btn.classList.add("is-active");

                var target = btn.getAttribute("data-scene");
                if (target === "auto") {
                    if (tempEl) tempEl.textContent = realState.temp;
                    if (condEl) condEl.textContent = realState.cond;
                    if (cityEl) cityEl.textContent = realState.city;
                    if (subEl) subEl.textContent = realState.isDay ? "Observations du jour" : "Observations nocturnes";
                    if (windEl) windEl.textContent = realState.windSpeed + " km/h " + getWindCardinal(realState.windDir);
                    if (windArrowEl) windArrowEl.style.transform = "rotate(" + (realState.windDir + 180) + "deg)";
                    if (humEl) humEl.textContent = (realState.humidity || 60) + "%";
                    if (precipEl) precipEl.textContent = (realState.precip || 0) + "%";
                    if (pressEl) pressEl.textContent = (realState.pressure || 1015) + " hPa";
                    applyScene(realState.type, realState.isDay);
                    return;
                }

                var previews = {
                    sunny: { temp: 22, cond: "Soleil radieux", dayOnly: true, wind: 8, dir: 180, hum: 45, precip: 0, press: 1022 },
                    cloudy: { temp: 15, cond: "Ciel très nuageux", wind: 16, dir: 240, hum: 70, precip: 15, press: 1015 },
                    rain: { temp: 12, cond: "Pluie battante", wind: 24, dir: 270, hum: 92, precip: 85, press: 1004 },
                    storm: { temp: 18, cond: "Orage violent", wind: 45, dir: 315, hum: 88, precip: 95, press: 998 },
                    snow: { temp: -1, cond: "Neige abondante", wind: 12, dir: 45, hum: 80, precip: 75, press: 1012 },
                    fog: { temp: 8, cond: "Brouillard épais", wind: 3, dir: 90, hum: 98, precip: 10, press: 1020 }
                };

                var data = previews[target];
                if (data) {
                    var isDay = (currentDayNight === "day");
                    if (data.dayOnly) isDay = true;
                    if (tempEl) tempEl.textContent = data.temp;
                    if (condEl) condEl.textContent = data.cond;
                    if (subEl) subEl.textContent = "Simulation d'ambiance";
                    if (windEl) windEl.textContent = data.wind + " km/h " + getWindCardinal(data.dir);
                    if (windArrowEl) windArrowEl.style.transform = "rotate(" + (data.dir + 180) + "deg)";
                    if (humEl) humEl.textContent = data.hum + "%";
                    if (precipEl) precipEl.textContent = data.precip + "%";
                    if (pressEl) pressEl.textContent = data.press + " hPa";
                    applyScene(target, isDay);
                }
            });
        });
    }

    if (timeBtn) {
        timeBtn.addEventListener("click", function () {
            var newIsDay = (currentDayNight !== "day");
            applyScene(currentWeatherType, newIsDay);
            if (pickerBtns[0] && pickerBtns[0].classList.contains("is-active")) {
                if (condEl) condEl.textContent = getWeatherDesc(realState.type === "sunny" ? 0 : 3, newIsDay);
            }
        });
    }
});
