import * as esbuild from 'esbuild'
esbuild.build({
    entryPoints: ['./resources/js/resized-column.js'],
    outfile: './resources/dist/js/resized-column.js',
    bundle: true,
    mainFields: ['module', 'main'],
    platform: 'neutral',
    treeShaking: true,
    target: ['es2020'],
    allowOverwrite: true,
    minify: true,
})


