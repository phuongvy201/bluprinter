/**
 * Enhanced Color Picker for Bluprinter
 * Supports advanced color display and selection
 */

class ColorPicker {
    constructor() {
        this.colorMap = {
            // Basic Colors
            black: "#000000",
            white: "#ffffff",
            red: "#dc2626",
            blue: "#2563eb",
            green: "#16a34a",
            yellow: "#eab308",
            purple: "#9333ea",
            pink: "#ec4899",
            orange: "#ea580c",
            brown: "#a16207",
            gray: "#6b7280",
            grey: "#6b7280",

            // Extended Colors
            navy: "#1e3a8a",
            maroon: "#991b1b",
            teal: "#0d9488",
            lime: "#65a30d",
            cyan: "#06b6d4",
            indigo: "#4f46e5",
            violet: "#8b5cf6",
            rose: "#f43f5e",
            amber: "#f59e0b",
            emerald: "#10b981",
            sky: "#0ea5e9",
            fuchsia: "#d946ef",

            // Dark Variants
            "dark chocolate": "#3c2415",
            "dark gray": "#374151",
            charcoal: "#374151",
            "dark blue": "#1e40af",
            "dark green": "#166534",
            "dark red": "#991b1b",

            // Light Variants
            "light gray": "#9ca3af",
            "light blue": "#93c5fd",
            "light green": "#86efac",
            "light pink": "#fbb6ce",
            "light yellow": "#fef3c7",
            cream: "#fef7cd",

            // Special Colors
            gold: "#fbbf24",
            silver: "#9ca3af",
            copper: "#b45309",
            bronze: "#92400e",
            platinum: "#6b7280",

            // Pattern Colors
            camo: "#365314",
            olive: "#65a30d",
            khaki: "#a3a3a3",
            beige: "#f5f5dc",
            tan: "#d2b48c",
            mint: "#a7f3d0",
            lavender: "#e9d5ff",
            coral: "#fda4af",
            turquoise: "#5eead4",

            // Additional Colors from UI
            "light blue": "#93c5fd",
            "sport grey": "#9ca3af",
            "dark heather": "#374151",
            "light pink": "#fbb6ce",
            "royal blue": "#1d4ed8",
            sand: "#fbbf24",
            "forest green": "#166534",
            "military green": "#365314",
            "ash grey": "#6b7280",
            natural: "#fef3c7",

            // Complete color set from user request
            black: "#000000",
            white: "#ffffff",
            "light blue": "#93c5fd",
            charcoal: "#374151",
            "sport grey": "#9ca3af",
            "dark heather": "#374151",
            navy: "#1e3a8a",
            maroon: "#991b1b",
            "light pink": "#fbb6ce",
            red: "#dc2626",
            "royal blue": "#1d4ed8",
            sand: "#fbbf24",
            "forest green": "#166534",
            "military green": "#365314",
            "ash grey": "#6b7280",
            purple: "#9333ea",
            orange: "#ea580c",
            natural: "#fef3c7",
        };

        this.init();
    }

    init() {
        this.enhanceColorSwatches();
        this.addColorPreview();
        this.addAccessibilityFeatures();
    }

    enhanceColorSwatches() {
        const colorSwatches = document.querySelectorAll(".color-swatch");

        colorSwatches.forEach((swatch) => {
            // Add ripple effect on click
            swatch.addEventListener("click", (e) => {
                this.createRippleEffect(e);
            });

            // Add keyboard navigation
            swatch.addEventListener("keydown", (e) => {
                this.handleKeyboardNavigation(e);
            });
        });
    }

    createRippleEffect(e) {
        const button = e.currentTarget;
        const ripple = document.createElement("span");
        const rect = button.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = e.clientX - rect.left - size / 2;
        const y = e.clientY - rect.top - size / 2;

        ripple.style.width = ripple.style.height = size + "px";
        ripple.style.left = x + "px";
        ripple.style.top = y + "px";
        ripple.classList.add("ripple");

        button.appendChild(ripple);

        setTimeout(() => {
            ripple.remove();
        }, 600);
    }

    handleKeyboardNavigation(e) {
        const swatches = Array.from(document.querySelectorAll(".color-swatch"));
        const currentIndex = swatches.indexOf(e.target);

        switch (e.key) {
            case "ArrowRight":
            case "ArrowDown":
                e.preventDefault();
                const nextIndex = (currentIndex + 1) % swatches.length;
                swatches[nextIndex].focus();
                break;
            case "ArrowLeft":
            case "ArrowUp":
                e.preventDefault();
                const prevIndex =
                    currentIndex === 0 ? swatches.length - 1 : currentIndex - 1;
                swatches[prevIndex].focus();
                break;
            case "Enter":
            case " ":
                e.preventDefault();
                e.target.click();
                break;
        }
    }

    addColorPreview() {
        // Create a larger color preview modal
        const previewModal = document.createElement("div");
        previewModal.id = "color-preview-modal";
        previewModal.className =
            "fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden";
        previewModal.innerHTML = `
            <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Color Preview</h3>
                    <button onclick="this.closest('#color-preview-modal').classList.add('hidden')" 
                            class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div id="color-preview-content" class="space-y-4">
                    <!-- Content will be populated dynamically -->
                </div>
            </div>
        `;
        document.body.appendChild(previewModal);

        // Add preview functionality to color swatches
        document.querySelectorAll(".color-swatch").forEach((swatch) => {
            swatch.addEventListener("dblclick", (e) => {
                this.showColorPreview(e.target);
            });
        });
    }

    showColorPreview(swatch) {
        const colorName = swatch.dataset.value;
        const colorCode = this.colorMap[colorName.toLowerCase()] || "#6b7280";

        const modal = document.getElementById("color-preview-modal");
        const content = document.getElementById("color-preview-content");

        content.innerHTML = `
            <div class="text-center">
                <div class="w-32 h-32 mx-auto rounded-full border-4 border-gray-200 mb-4 shadow-lg" 
                     style="background: ${colorCode}; background-image: linear-gradient(45deg, ${colorCode}cc, ${colorCode});">
                </div>
                <h4 class="text-xl font-semibold text-gray-900 mb-2">${colorName}</h4>
                <p class="text-gray-600 mb-4">Color Code: <code class="bg-gray-100 px-2 py-1 rounded">${colorCode}</code></p>
                <div class="space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Hex:</span>
                        <span class="font-mono text-sm">${colorCode}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">RGB:</span>
                        <span class="font-mono text-sm">${this.hexToRgb(
                            colorCode
                        )}</span>
                    </div>
                </div>
            </div>
        `;

        modal.classList.remove("hidden");
    }

    hexToRgb(hex) {
        const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
        return result
            ? `rgb(${parseInt(result[1], 16)}, ${parseInt(
                  result[2],
                  16
              )}, ${parseInt(result[3], 16)})`
            : "Invalid hex";
    }

    addAccessibilityFeatures() {
        // Add ARIA labels and roles
        document.querySelectorAll(".color-swatch").forEach((swatch, index) => {
            swatch.setAttribute("role", "button");
            swatch.setAttribute("tabindex", "0");
            swatch.setAttribute(
                "aria-label",
                `Select color: ${swatch.dataset.value}`
            );

            if (swatch.classList.contains("border-[#005366]")) {
                swatch.setAttribute("aria-pressed", "true");
            } else {
                swatch.setAttribute("aria-pressed", "false");
            }
        });

        // Add color contrast checking
        this.checkColorContrast();
    }

    checkColorContrast() {
        document.querySelectorAll(".color-swatch").forEach((swatch) => {
            const colorName = swatch.dataset.value.toLowerCase();
            const colorCode = this.colorMap[colorName];

            if (colorCode) {
                const luminance = this.getLuminance(colorCode);
                const needsBorder = luminance > 0.5; // Light colors need border

                if (
                    needsBorder &&
                    !colorName.includes("white") &&
                    !colorName.includes("cream")
                ) {
                    swatch.style.borderColor = "#d1d5db"; // gray-300
                }
            }
        });
    }

    getLuminance(hex) {
        const rgb = this.hexToRgb(hex);
        const values = rgb.match(/\d+/g).map(Number);
        const [r, g, b] = values.map((v) => {
            v = v / 255;
            return v <= 0.03928
                ? v / 12.92
                : Math.pow((v + 0.055) / 1.055, 2.4);
        });
        return 0.2126 * r + 0.7152 * g + 0.0722 * b;
    }

    // Public method to get color code
    getColorCode(colorName) {
        return this.colorMap[colorName.toLowerCase()] || "#6b7280";
    }

    // Public method to add custom colors
    addCustomColor(name, hex) {
        this.colorMap[name.toLowerCase()] = hex;
    }
}

// CSS for ripple effect
const style = document.createElement("style");
style.textContent = `
    .ripple {
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.3);
        transform: scale(0);
        animation: ripple-animation 0.6s linear;
        pointer-events: none;
    }
    
    @keyframes ripple-animation {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
    
    .color-swatch:focus {
        outline: 2px solid #005366;
        outline-offset: 2px;
    }
    
    .color-swatch[aria-pressed="true"] {
        transform: scale(1.1);
        box-shadow: 0 0 0 2px #005366, 0 4px 12px rgba(0, 83, 102, 0.3);
    }
`;
document.head.appendChild(style);

// Initialize when DOM is loaded
document.addEventListener("DOMContentLoaded", function () {
    window.colorPicker = new ColorPicker();
});

// Export for module usage
if (typeof module !== "undefined" && module.exports) {
    module.exports = ColorPicker;
}
