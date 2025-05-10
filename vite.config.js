import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig(({ command, mode }) => {
    // Cargar variables de entorno
    const env = loadEnv(mode, process.cwd(), 'VITE_');

    return {
        plugins: [
            laravel({
                input: ['resources/css/app.css', 'resources/js/app.js'],
                refresh: true,
            }),
        ],
        server: {
            cors: {
                origin: env.VITE_ALLOWED_ORIGINS?.split(',') || ['http://localhost:5173'],
                methods: ['GET', 'POST', 'PUT', 'DELETE'],
                credentials: true
            },
            host: env.VITE_HOST || 'localhost',
            strictPort: true,
            port: parseInt(env.VITE_PORT || '5173')
        }
    };
});
