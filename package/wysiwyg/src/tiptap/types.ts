import type { TiptapEditor } from './editor.ts'

import { basicStyleModule } from './module/basicStyle.ts'
import { cleanupModule } from './module/cleanup.ts'
import { WysiwygProfile } from '../wysiwyg/wysiwyg.ts'
import { ExtensionType } from './extensions.ts'
import { TranslationKey } from './translations.ts'

export type ContextType = 'table'

export const Modules: TiptapModule[] = [
    ...basicStyleModule,
    cleanupModule,
]

export interface HtmlTransform {
    name: string
    toEditor?: (doc: Document) => void
    toOutput?: (doc: Document) => void
}

export interface ContextMenuItem {
    label: TranslationKey
    icon?: string
    parent?: TranslationKey
    parentIcon?: string
    order?: number
    command: (e: TiptapEditor, ctx?: { target?: Element | null }) => void
    disabled?: (editor: TiptapEditor) => boolean
}

export interface ContextMenu {
    node?: string
    selector?: string
    order?: number
    items: ContextMenuItem[]
}

export interface ToolbarItem {
    name: string
    icon: string
    tooltip: TranslationKey
    order?: number
    extensions?: ExtensionType[]
    command: (editor: TiptapEditor) => void
    isActive?: (editor: TiptapEditor) => boolean
    isDisabled?: (editor: TiptapEditor) => boolean
}

export interface ToolbarItemCustom {
    name: string
    create: (editor: TiptapEditor) => HTMLElement
    destroy?: (editor: TiptapEditor) => void
}

export interface Toolbar {
    group?: string
    items: (ToolbarItem | ToolbarItemCustom)[]
}

export interface TiptapModule {
    extensions?: ExtensionType[] | ((editor: TiptapEditor) => ExtensionType[])
    toolbar?: Toolbar
    contextMenu?: ContextMenu
    htmlTransforms?: HtmlTransform[]
    isEnabled?: (profile: WysiwygProfile) => boolean
}
