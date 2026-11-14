/**
 * WeatherHeader.js — Contrôleur d'En-tête Météo Vectoriel Vivant
 * Appel direct Open-Meteo haute performance (0 dépendance externe).
 */
document.addEventListener("DOMContentLoaded", function () {
    var scene = document.getElementById("meteo_living_scene");
    if (!scene) return;

    var defaultCity = scene.getAttribute("data-city") || "Paris";
    var lat = parseFloat(scene.getAttribute("data-lat")) || 48.8566;
    var lon = parseFloat(scene.getAttribute("data-lon")) || 2.3522;

    var tempEl = scene.querySelector('[data-role="ws-temp"]');
    var condEl = scene.querySelector('[data-role="ws-condition"]');
    var cityEl = scene.querySelector('[data-role="ws-city"]');
    var subEl = scene.querySelector('[data-role="ws-sub"]');
    var pickerBtns = scene.querySelectorAll(".ws-pick-btn");
    var timeBtn = document.getElementById("ws_time_toggle");

    var currentDayNight = "day";
    var currentWeatherType = "sunny";
    var realState = { type: "sunny", isDay: true, temp: 16, cond: "Ensoleillé", city: defaultCity };

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

    // Appel direct à Open-Meteo
    var openMeteoUrl = "https://api.open-meteo.com/v1/forecast?latitude=" + lat + "&longitude=" + lon + "&current=temperature_2m,weather_code,is_day&timezone=auto";

    fetch(openMeteoUrl)
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

            realState = {
                type: determineWeatherType(code),
                isDay: isDay,
                temp: temp,
                cond: getWeatherDesc(code, isDay),
                city: defaultCity
            };

            if (tempEl) tempEl.textContent = realState.temp;
            if (condEl) condEl.textContent = realState.cond;
            if (cityEl) cityEl.textContent = realState.city;
            if (subEl) subEl.textContent = isDay ? "Observations du jour" : "Observations nocturnes";

            applyScene(realState.type, realState.isDay);
        })
        .catch(function () {
            fallbackDefault();
        });

    function fallbackDefault() {
        var hour = new Date().getHours();
        var isDay = hour >= 7 && hour < 20;
        realState = {
            type: "sunny",
            isDay: isDay,
            temp: isDay ? 16 : 11,
            cond: isDay ? "Ensoleillé" : "Nuit claire étoilée",
            city: defaultCity
        };
        if (tempEl) tempEl.textContent = realState.temp;
        if (condEl) condEl.textContent = realState.cond;
        if (subEl) subEl.textContent = isDay ? "Journée estimée" : "Nuit estimée";
        applyScene(realState.type, realState.isDay);
    }

    // Sélecteur d'ambiances
    pickerBtns.forEach(function (btn) {
        btn.addEventListener("click", function () {
            pickerBtns.forEach(function (b) { b.classList.remove("is-active"); });
            btn.classList.add("is-active");

            var target = btn.getAttribute("data-scene");
            if (target === "auto") {
                if (tempEl) tempEl.textContent = realState.temp;
                if (condEl) condEl.textContent = realState.cond;
                if (subEl) subEl.textContent = realState.isDay ? "Observations du jour" : "Observations nocturnes";
                applyScene(realState.type, realState.isDay);
            } else {
                var isDay = (currentDayNight === "day");
                var descMap = {
                    sunny: isDay ? "Plein soleil" : "Nuit claire étoilée",
                    cloudy: isDay ? "Ciel couvert" : "Nuit couverte",
                    rain: isDay ? "Pluie battante" : "Pluie de nuit",
                    storm: isDay ? "Orage grondant" : "Orage de nuit",
                    snow: isDay ? "Chute de neige" : "Neige de nuit",
                    fog: isDay ? "Brouillard épais" : "Brume de nuit"
                };
                if (condEl) condEl.textContent = descMap[target] || target;
                if (subEl) subEl.textContent = "Aperçu interactif";
                applyScene(target, isDay);
            }
        });
    });

    // Bascule Jour / Nuit
    if (timeBtn) {
        timeBtn.addEventListener("click", function () {
            var newIsDay = (currentDayNight !== "day");
            applyScene(currentWeatherType, newIsDay);

            var descMap = {
                sunny: newIsDay ? "Plein soleil" : "Nuit claire étoilée",
                cloudy: newIsDay ? "Ciel couvert" : "Nuit couverte",
                rain: newIsDay ? "Pluie battante" : "Pluie de nuit",
                storm: newIsDay ? "Orage grondant" : "Orage de nuit",
                snow: newIsDay ? "Chute de neige" : "Neige de nuit",
                fog: newIsDay ? "Brouillard épais" : "Brume de nuit"
            };
            if (condEl) condEl.textContent = descMap[currentWeatherType] || currentWeatherType;
            if (subEl) subEl.textContent = newIsDay ? "Ambiance Jour" : "Ambiance Nuit";
        });
    }
});
