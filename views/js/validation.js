document.addEventListener("DOMContentLoaded", function () {
    if (typeof isPro !== "undefined" && isPro) {
        const form = document.querySelector("form#customer-form");

        if (form) {
            form.addEventListener("submit", function (event) {
                const requiredFields = {
                    company: "Raison Sociale",
                    siret: "SIRET",
                    address: "Adresse",
                    zipcode: "Code Postal",
                    city: "Ville",
                    country: "Pays",
                    kbis_file: "KBIS",
                };

                for (const [fieldName, fieldLabel] of Object.entries(
                    requiredFields
                )) {
                    const input = form.querySelector(`[name="${fieldName}"]`);
                    if (!input || !input.value.trim()) {
                        event.preventDefault();
                        alert(
                            `Le champ ${fieldLabel} est obligatoire pour un compte professionnel.`
                        );
                        if (input) {
                            input.focus();
                        }
                        return;
                    }
                }

                // Check KBIS file
                const kbisInput = form.querySelector('[name="kbis_file"]');
                if (!kbisInput || !kbisInput.files || !kbisInput.files.length) {
                    event.preventDefault();
                    alert(
                        "Le fichier KBIS est obligatoire pour un compte professionnel."
                    );
                    if (kbisInput) {
                        kbisInput.focus();
                    }
                    return;
                }
            });
        }
    }
});
