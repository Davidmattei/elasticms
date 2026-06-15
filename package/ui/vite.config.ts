import { defineConfig } from 'vite'
import { transform } from 'esbuild'
import { resolve } from 'node:path'
import dts from 'vite-plugin-dts'

export default defineConfig({
    build: {
        lib: {
            entry: resolve(__dirname, 'src/index.ts'),
            formats: ['es'],
            fileName: 'index',
        }
    },
    plugins: [
        dts({ include: ['src'] }),
        {
            name: 'minifyEs',
            renderChunk: {
                order: 'post',
                async handler(code) {
                    return await transform(code, { minify: true })
                }
            }
        }
    ]
})