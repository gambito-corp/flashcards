// resources/js/components/resizableGrid.ts
interface ResizableGridInterface {
    init: () => void;
    destroy: () => void;
}

export const createResizableGrid = (): ResizableGridInterface => {
    // Elementos del DOM
    let grid: HTMLElement | null = null;
    let handle: HTMLElement | null = null;
    let sidebar: HTMLElement | null = null;
    let toggleButton: HTMLElement | null = null;

    // Estado del componente
    let isDragging = false;
    let isOpen = true;
    let startX = 0;
    let startWidth = 0;
    let lastWidthPercent = 25;

    // Manejadores de eventos (bindeados para mantener el contexto)
    const boundOnMouseDown = onMouseDown.bind(null);
    const boundOnMouseMove = onMouseMove.bind(null);
    const boundOnMouseUp = onMouseUp.bind(null);
    const boundToggleSidebar = toggleSidebar.bind(null);

    function initializeElements() {
        grid = document.getElementById('resizable-grid');
        handle = document.getElementById('drag-handle');
        sidebar = document.getElementById('sidebar');
        toggleButton = document.getElementById('toggle-sidebar');
    }

    function addEventListeners() {
        handle?.addEventListener('mousedown', boundOnMouseDown);
        document.addEventListener('mousemove', boundOnMouseMove);
        document.addEventListener('mouseup', boundOnMouseUp);
        toggleButton?.addEventListener('click', boundToggleSidebar);
    }

    function removeEventListeners() {
        handle?.removeEventListener('mousedown', boundOnMouseDown);
        document.removeEventListener('mousemove', boundOnMouseMove);
        document.removeEventListener('mouseup', boundOnMouseUp);
        toggleButton?.removeEventListener('click', boundToggleSidebar);
    }

    function onMouseDown(e: MouseEvent) {
        if (!isOpen || !grid) return;

        isDragging = true;
        startX = e.clientX;

        const currentWidth = window.getComputedStyle(grid)
            .gridTemplateColumns.split(' ')[0];
        startWidth = grid.offsetWidth * (parseFloat(currentWidth) / 100);

        document.body.style.userSelect = 'none';
    }

    function onMouseMove(e: MouseEvent) {
        if (!isDragging || !isOpen || !grid) return;

        const containerWidth = grid.offsetWidth;
        const deltaX = e.clientX - startX;
        const newWidthPercent = ((startWidth + deltaX) / containerWidth) * 100;

        const clampedWidth = Math.max(10, Math.min(25, newWidthPercent));
        grid.style.gridTemplateColumns = `${clampedWidth}% ${100 - clampedWidth}%`;
        lastWidthPercent = clampedWidth;
    }

    function onMouseUp() {
        isDragging = false;
        document.body.style.userSelect = '';
    }

    function toggleSidebar() {
        if (!grid) return;

        isOpen = !isOpen;

        if (isOpen) {
            grid.style.gridTemplateColumns = `${lastWidthPercent}% ${100 - lastWidthPercent}%`;
        } else {
            const currentWidth = parseFloat(
                window.getComputedStyle(grid)
                    .gridTemplateColumns.split(' ')[0]
            );
            lastWidthPercent = currentWidth;
            grid.style.gridTemplateColumns = '0% 100%';
        }
    }

    return {
        init() {
            console.log('Inicializando ResizableGrid...');
            initializeElements();

            if (!grid || !handle || !sidebar || !toggleButton) {
                console.error('Error: Elementos requeridos no encontrados');
                return;
            }

            addEventListeners();
        },

        destroy() {
            console.log('Destruyendo ResizableGrid...');
            removeEventListeners();

            // Resetear elementos
            grid = null;
            handle = null;
            sidebar = null;
            toggleButton = null;
        }
    };
};
