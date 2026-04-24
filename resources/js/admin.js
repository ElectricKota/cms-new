import { Application, Controller } from '@hotwired/stimulus'
import Nette from 'nette-forms'
import AirDatepicker from 'air-datepicker'
import localeCs from 'air-datepicker/locale/cs'
import 'air-datepicker/air-datepicker.css'
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
