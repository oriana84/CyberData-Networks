"use strict";

(function () {
    const datos = window.datosDashboard || { criticidad: [], estado: [] };

    const coloresCriticidad = {
        CRITICA: "#dc2626",
        ALTA: "#f97316",
        MEDIA: "#eab308",
        BAJA: "#16a34a"
    };

    const coloresEstado = {
        ABIERTO: "#2563eb",
        "EN PROCESO": "#f59e0b",
        CERRADO: "#16a34a",
        "SIN ESTADO": "#64748b"
    };

    function normalizar(texto) {
        return String(texto || "SIN ESTADO").trim().toUpperCase();
    }

    function crearMensajeVacio(contenedor, mensaje) {
        const elemento = document.createElement("p");
        elemento.className = "grafico-sin-datos";
        elemento.textContent = mensaje;
        contenedor.replaceChildren(elemento);
    }

    function generarTorta() {
        const grafico = document.getElementById("graficoCriticidad");
        const leyenda = document.getElementById("leyendaCriticidad");

        if (!grafico || !leyenda) return;

        const registros = Array.isArray(datos.criticidad) ? datos.criticidad : [];
        const total = registros.reduce((suma, fila) => suma + Number(fila.total || 0), 0);

        if (total <= 0) {
            grafico.classList.add("grafico-vacio");
            leyenda.textContent = "No existen incidentes registrados.";
            return;
        }

        let acumulado = 0;
        const segmentos = [];
        const fragmento = document.createDocumentFragment();

        registros.forEach((fila) => {
            const etiqueta = normalizar(fila.criticidad);
            const valor = Number(fila.total || 0);
            if (valor <= 0) return;

            const porcentaje = (valor / total) * 100;
            const inicio = acumulado;
            acumulado += porcentaje;
            const color = coloresCriticidad[etiqueta] || "#64748b";

            segmentos.push(`${color} ${inicio.toFixed(2)}% ${acumulado.toFixed(2)}%`);

            const item = document.createElement("div");
            item.className = "leyenda-item";

            const muestra = document.createElement("span");
            muestra.className = "leyenda-color";
            muestra.style.backgroundColor = color;

            const texto = document.createElement("span");
            texto.className = "leyenda-texto";
            texto.textContent = `${etiqueta}: ${valor} (${porcentaje.toFixed(1)}%)`;

            item.append(muestra, texto);
            fragmento.appendChild(item);
        });

        grafico.style.background = `conic-gradient(${segmentos.join(", ")})`;
        leyenda.replaceChildren(fragmento);
    }

    function generarBarras() {
        const contenedor = document.getElementById("graficoEstado");
        if (!contenedor) return;

        const registros = Array.isArray(datos.estado) ? datos.estado : [];
        const maximo = Math.max(0, ...registros.map((fila) => Number(fila.total || 0)));

        if (maximo <= 0) {
            crearMensajeVacio(contenedor, "No existen estados para representar.");
            return;
        }

        const fragmento = document.createDocumentFragment();

        registros.forEach((fila) => {
            const etiqueta = normalizar(fila.estado_actual);
            const valor = Number(fila.total || 0);
            const porcentaje = (valor / maximo) * 100;

            const filaBarra = document.createElement("div");
            filaBarra.className = "barra-fila";

            const cabecera = document.createElement("div");
            cabecera.className = "barra-cabecera";

            const nombre = document.createElement("span");
            nombre.textContent = etiqueta;

            const numero = document.createElement("strong");
            numero.textContent = String(valor);

            cabecera.append(nombre, numero);

            const pista = document.createElement("div");
            pista.className = "barra-pista";

            const barra = document.createElement("div");
            barra.className = "barra-valor";
            barra.style.width = `${porcentaje.toFixed(2)}%`;
            barra.style.backgroundColor = coloresEstado[etiqueta] || "#0f4c81";
            barra.setAttribute("aria-label", `${etiqueta}: ${valor} incidentes`);

            pista.appendChild(barra);
            filaBarra.append(cabecera, pista);
            fragmento.appendChild(filaBarra);
        });

        contenedor.replaceChildren(fragmento);
    }

    document.addEventListener("DOMContentLoaded", function () {
        generarTorta();
        generarBarras();
    });
})();
