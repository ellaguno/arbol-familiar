// Dependencias del árbol genealógico (D3 v7 + d3-flextree), bundleadas por Vite
// en lugar de cargarse desde un CDN externo (elimina la dependencia de red en
// runtime: mejora fiabilidad, privacidad y compatibilidad con CSP estricta).
//
// El script del árbol (inline en tree/view.blade.php) usa el global `window.d3`
// y llama `d3.flextree()`, igual que con los builds UMD del CDN. Como el árbol
// inicializa en DOMContentLoaded y los módulos de Vite se ejecutan (deferred)
// antes de ese evento, `window.d3` ya está disponible cuando corre initTree().
import * as d3ns from 'd3';
import { flextree } from 'd3-flextree';

// El namespace de un módulo ESM es de solo lectura; copiar a un objeto mutable
// para poder adjuntar flextree como hacía el plugin UMD (d3.flextree).
const d3 = { ...d3ns, flextree };

window.d3 = d3;
