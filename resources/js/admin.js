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

const application = Application.start()

application.register('tinymce', class extends Controller {
  connect () {
    tinymce.init({
      target: this.element,
      menubar: false,
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
