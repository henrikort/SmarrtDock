document.addEventListener("DOMContentLoaded", function () {
    const menuIcon = document.querySelector(".menu-icon");
    const menuEscondido = document.querySelector(".menu-escondido");

    menuIcon.addEventListener("click", function () {
        if (menuEscondido.classList.contains("show")) {
            // Se já estiver visível, remove a classe e faz a animação reversa
            menuEscondido.style.opacity = "0";
            menuEscondido.style.transform = "translateX(100%)";
            setTimeout(() => {
                menuEscondido.classList.remove("show");
                menuEscondido.style.display = "none";
                body.style.overflow = ""; // Permite rolagem normal
                body.style.overflowX = "";

            }, 300); // Tempo da transição
        } else {
            // Se estiver oculto, exibe e faz a animação de entrada
            menuEscondido.style.display = "block";
            setTimeout(() => {
                menuEscondido.classList.add("show");
                menuEscondido.style.opacity = "1";
                menuEscondido.style.transform = "translateX(0)";
                body.style.overflow = "hidden"; // ✅ Bloqueia rolagem geral
                body.style.overflowX = "hidden"; // ✅ Impede rolagem horizontal

            }, 10);
        }
    });
});
