declare module '@inertiaui/modal-vue' {
  import type { DefineComponent } from 'vue'
  export const Modal: DefineComponent<{}, {}, any>
  export const ModalLink: DefineComponent<{}, {}, any>
  export function renderApp(...args: any[]): any
  export function putConfig(...args: any[]): any
}
