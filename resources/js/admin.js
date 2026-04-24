import { Application, Controller } from '@hotwired/stimulus'
import Nette from 'nette-forms'
import AirDatepicker from 'air-datepicker'
import localeCs from 'air-datepicker/locale/cs'
import 'air-datepicker/air-datepicker.css'
import lightbox from 'lightbox2'
import 'lightbox2/dist/css/lightbox.css'
import '../styles/admin.css'
import tinymce from 'tinymce/tinymce'
import 'tinymce/icons/default'
import 'tinymce/themes/silver'
import 'tinymce/models/dom'
import 'tinymce/plugins/link'
import 'tinymce/plugins/lists'
import 'tinymce/plugins/table'
import 'tinymce/plugins/code'
import 'tinymce/skins/ui/oxide/skin.min.css'
import 'tinymce/skins/ui/oxide/content.min.css'

const application = Application.start()

tinymce.addI18n('cs', {
  Redo: 'Znovu',
  Undo: 'Zpět',
  Cut: 'Vyjmout',
  Copy: 'Kopírovat',
  Paste: 'Vložit',
  Bold: 'Tučně',
  Italic: 'Kurzíva',
  Link: 'Odkaz',
  Table: 'Tabulka',
  Code: 'Kód',
  Blocks: 'Bloky',
  Paragraph: 'Odstavec',
  'Heading 1': 'Nadpis 1',
  'Heading 2': 'Nadpis 2',
  'Heading 3': 'Nadpis 3',
  'Bullet list': 'Odrážky',
  'Numbered list': 'Číslování'
})

application.register('tinymce', class extends Controller {
  connect () {
    tinymce.init({
      target: this.element,
      language: 'cs',
      menubar: false,
      skin: false,
      content_css: false,
      content_style: 'body{font-family:Inter,system-ui,sans-serif;font-size:16px;line-height:1.55;color:#161a1d}',
      plugins: 'link lists table code',
      toolbar: 'undo redo | blocks | bold italic | bullist numlist | link table | code',
      license_key: 'gpl',
      promotion: false,
      branding: false
    })
  }

  disconnect () {
    tinymce.remove(this.element)
  }
})

const initDatepickers = () => {
  document.querySelectorAll('[data-controller~="datepicker"]').forEach((element) => {
    if (element instanceof HTMLInputElement && element.dataset.datepickerReady !== 'true') {
      const datepicker = new AirDatepicker(element, {
        locale: localeCs,
        autoClose: true,
        buttons: ['today', 'clear'],
        dateFormat: 'dd.MM.yyyy',
        position: 'bottom left'
      })
      element.dataset.datepickerReady = 'true'
      element.addEventListener('focus', () => datepicker.show())
      element.addEventListener('click', () => datepicker.show())
    }
  })
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initDatepickers)
} else {
  initDatepickers()
}

lightbox.option({
  albumLabel: 'Fotka %1 z %2',
  fadeDuration: 160,
  imageFadeDuration: 160,
  resizeDuration: 180,
  wrapAround: true
})

const initFileDrops = () => {
  document.querySelectorAll('input[type="file"]').forEach((input) => {
    if (!(input instanceof HTMLInputElement) || input.dataset.filedropReady === 'true') {
      return
    }

    input.dataset.filedropReady = 'true'
    if (!input.id) {
      input.id = `filedrop-${Math.random().toString(36).slice(2)}`
    }

    input.classList.add('filedrop__input')

    const dropzone = document.createElement('label')
    dropzone.className = 'filedrop'
    dropzone.htmlFor = input.id

    const title = document.createElement('span')
    title.className = 'filedrop__title'
    title.textContent = 'Přetáhněte fotky sem'

    const hint = document.createElement('span')
    hint.className = 'filedrop__hint'
    hint.textContent = 'nebo klikněte a vyberte soubory z počítače'

    const files = document.createElement('span')
    files.className = 'filedrop__files'
    files.textContent = 'Soubor nevybrán'

    input.parentNode?.insertBefore(dropzone, input)
    dropzone.append(title, hint, files, input)

    const refreshFiles = () => {
      const selected = Array.from(input.files ?? [])
      files.textContent = selected.length > 0
        ? selected.map((file) => file.name).join(', ')
        : 'Soubor nevybrán'
    }

    input.addEventListener('change', refreshFiles)

    dropzone.addEventListener('dragover', (event) => {
      event.preventDefault()
      dropzone.classList.add('is-dragover')
    })

    dropzone.addEventListener('dragleave', () => {
      dropzone.classList.remove('is-dragover')
    })

    dropzone.addEventListener('drop', (event) => {
      event.preventDefault()
      dropzone.classList.remove('is-dragover')

      if (event.dataTransfer?.files.length) {
        input.files = event.dataTransfer.files
        input.dispatchEvent(new Event('change', { bubbles: true }))
      }
    })
  })
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initFileDrops)
} else {
  initFileDrops()
}

const deleteDialog = document.querySelector('[data-delete-dialog]')

if (deleteDialog instanceof HTMLDialogElement) {
  const confirmButton = deleteDialog.querySelector('[data-delete-dialog-confirm]')
  const cancelButton = deleteDialog.querySelector('[data-delete-dialog-cancel]')
  const message = deleteDialog.querySelector('[data-delete-dialog-message]')
  let deleteHref = null

  document.addEventListener('click', (event) => {
    if (!(event.target instanceof Element)) {
      return
    }

    const trigger = event.target.closest('[data-confirm-delete]')
    if (!(trigger instanceof HTMLAnchorElement)) {
      return
    }

    event.preventDefault()
    deleteHref = trigger.href

    if (message !== null) {
      message.textContent = trigger.dataset.confirmDelete || 'Opravdu chcete položku smazat?'
    }

    if (!deleteDialog.open) {
      deleteDialog.showModal()
    }
  })

  cancelButton?.addEventListener('click', () => {
    deleteHref = null
    deleteDialog.close()
  })

  confirmButton?.addEventListener('click', () => {
    if (deleteHref !== null) {
      window.location.href = deleteHref
    }
  })

  deleteDialog.addEventListener('click', (event) => {
    if (event.target === deleteDialog) {
      deleteHref = null
      deleteDialog.close()
    }
  })

  deleteDialog.addEventListener('close', () => {
    deleteHref = null
  })
}

Nette.initOnLoad()
