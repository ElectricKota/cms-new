import '../styles/front.css'
import { Application } from '@hotwired/stimulus'
import Nette from 'nette-forms'

window.Stimulus = Application.start()
Nette.initOnLoad()
