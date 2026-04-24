import '../styles/admin.css'
import { Application, Controller } from '@hotwired/stimulus'
import Nette from 'nette-forms'
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
      promotion: false,
      branding: false
    })
  }

  disconnect () {
    tinymce.remove(this.element)
  }
})

Nette.initOnLoad()
