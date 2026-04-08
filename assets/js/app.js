const body = document.body;
const menuToggle = document.querySelector(".menu-toggle");
const navLinks = document.querySelectorAll(".nav-links a");
const revealItems = document.querySelectorAll(".reveal");
const roleTabs = document.querySelectorAll(".role-tab");
const rolePanels = document.querySelectorAll(".role-panel");
const contactForm = document.querySelector(".contact-form-modern");
const statusLine = document.querySelector(".status-line");
const roleSelect = document.querySelector("#role_code");
const roleFormSections = document.querySelectorAll("[data-role-section]");
const roleRequiredInputs = document.querySelectorAll("[data-role-required]");
const syncInputs = document.querySelectorAll("[data-sync-target]");

if (menuToggle) {
    menuToggle.addEventListener("click", () => {
        body.classList.toggle("nav-open");
    });
}

navLinks.forEach((link) => {
    link.addEventListener("click", () => {
        body.classList.remove("nav-open");
    });
});

if (revealItems.length > 0) {
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add("is-visible");
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.18 }
    );

    revealItems.forEach((item) => observer.observe(item));
}

roleTabs.forEach((tab) => {
    tab.addEventListener("click", () => {
        const target = tab.dataset.roleTarget;

        roleTabs.forEach((item) => item.classList.remove("is-active"));
        rolePanels.forEach((panel) => panel.classList.remove("is-active"));

        tab.classList.add("is-active");

        const activePanel = document.querySelector(`[data-role-panel="${target}"]`);

        if (activePanel) {
            activePanel.classList.add("is-active");
        }
    });
});

if (contactForm && statusLine && contactForm.dataset.demoOnly === "true") {
    contactForm.addEventListener("submit", (event) => {
        event.preventDefault();
        statusLine.textContent = "Brief envoye. La prochaine etape peut etre le login, le dashboard ou le CRUD.";
        contactForm.reset();
    });
}

const updateRegisterRoleUI = () => {
    if (!roleSelect) {
        return;
    }

    const role = roleSelect.value;

    roleFormSections.forEach((section) => {
        section.classList.toggle("is-active", section.dataset.roleSection === role);
    });

    roleRequiredInputs.forEach((input) => {
        input.required = input.dataset.roleRequired === role;
    });
};

if (roleSelect) {
    updateRegisterRoleUI();

    roleSelect.addEventListener("change", updateRegisterRoleUI);
}

syncInputs.forEach((input) => {
    input.addEventListener("input", () => {
        const target = input.dataset.syncTarget;
        const hiddenInput = document.querySelector(`input[name="${target}"], textarea[name="${target}"]`);

        if (hiddenInput) {
            hiddenInput.value = input.value;
        }
    });
});
