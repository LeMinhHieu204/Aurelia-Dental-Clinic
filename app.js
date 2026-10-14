const app = document.querySelector('#app');

const images = {
  hero: 'https://images.unsplash.com/photo-1609840114035-3c981b782dfe?auto=format&fit=crop&w=1800&q=88',
  clinic: 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?auto=format&fit=crop&w=1200&q=86',
  consultation: 'https://images.unsplash.com/photo-1606811971618-4486d14f3f99?auto=format&fit=crop&w=1200&q=86',
  aligners: 'https://images.unsplash.com/photo-1609840114035-3c981b782dfe?auto=format&fit=crop&w=700&q=82',
  implants: 'https://images.unsplash.com/photo-1588776814546-daab30f310ce?auto=format&fit=crop&w=700&q=82',
  whitening: 'https://images.unsplash.com/photo-1606265752439-1f18756aa2c4?auto=format&fit=crop&w=700&q=82',
  tools: 'https://images.unsplash.com/photo-1588776814546-daab30f310ce?auto=format&fit=crop&w=700&q=82',
  smile: 'https://images.unsplash.com/photo-1550831107-1553da8c8464?auto=format&fit=crop&w=700&q=82',
  reception: 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?auto=format&fit=crop&w=1000&q=82',
  doctor1: 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?auto=format&fit=crop&w=600&q=88',
  doctor2: 'https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?auto=format&fit=crop&w=600&q=88',
  doctor3: 'https://images.unsplash.com/photo-1594824476967-48c8b964273f?auto=format&fit=crop&w=600&q=88',
  doctor4: 'https://images.unsplash.com/photo-1622253692010-333f2da6031d?auto=format&fit=crop&w=600&q=88',
};

const treatments = [
  { slug: 'clear-aligners', name: 'Clear Aligners', short: 'A discreet, consultation-led way to explore a straighter smile.', image: images.aligners, label: 'Smile alignment', meta: 'Consultation required', details: 'Clear aligner treatment uses a series of removable trays planned around your individual needs. A qualified clinician will explain suitability, timelines, and alternatives during consultation.' },
  { slug: 'dental-implants', name: 'Dental Implants', short: 'A considered option for replacing missing teeth and restoring function.', image: images.implants, label: 'Restorative care', meta: 'Treatment planning', details: 'Implant care is planned after a full assessment of your oral health, medical history, and goals. Your clinician will explain the stages, alternatives, and limitations before treatment.' },
  { slug: 'teeth-whitening', name: 'Teeth Whitening', short: 'Professional guidance for a brighter smile, with safety at the centre.', image: images.whitening, label: 'Cosmetic care', meta: 'Clinician-guided', details: 'Whitening options depend on your teeth, gums, existing restorations, and desired result. We begin with an assessment and explain what professional whitening can and cannot change.' },
  { slug: 'general-dentistry', name: 'General Dentistry', short: 'Everyday dental care for individuals and families, from check-ups onward.', image: images.tools, label: 'Essential care', meta: 'Ongoing support', details: 'General dentistry includes examinations, preventive advice, restorative care, and referrals where appropriate. Your clinician will tailor the visit to your needs.' },
  { slug: 'veneers', name: 'Veneers', short: 'A careful cosmetic conversation about shape, shade, and long-term care.', image: images.smile, label: 'Cosmetic care', meta: 'Consultation required', details: 'Veneers are considered only after a clinical assessment and a discussion of more conservative options. Material choices, maintenance, and limitations are part of the plan.' },
  { slug: 'smile-makeovers', name: 'Smile Makeovers', short: 'A coordinated plan that brings several appropriate options together.', image: images.consultation, label: 'Personalised planning', meta: 'Case-by-case', details: 'A smile makeover is not a one-size-fits-all package. We listen to your goals, assess your oral health, and explain which options are suitable for you.' },
  { slug: 'preventive-care', name: 'Preventive Care', short: 'Practical support to help you look after your teeth and gums over time.', image: images.clinic, label: 'Healthy habits', meta: 'Regular reviews', details: 'Preventive appointments focus on assessment, education, and early identification of concerns. Frequency is discussed with your dental team.' },
  { slug: 'dental-hygiene', name: 'Dental Hygiene', short: 'Professional cleaning and personalised advice for a confident daily routine.', image: images.tools, label: 'Gum health', meta: 'Personalised advice', details: 'Hygiene visits may include cleaning, gum-health guidance, and tailored home-care advice. Your clinician will explain what is appropriate for you.' },
];

const doctors = [
  { slug: 'emily-carter', name: 'Dr. Emily Carter', role: 'Cosmetic & General Dentistry', image: images.doctor1, intro: 'A calm, collaborative approach to everyday and cosmetic dental conversations.' },
  { slug: 'james-miller', name: 'Dr. James Miller', role: 'Restorative & Implant Dentistry', image: images.doctor2, intro: 'Focused on clear explanations, careful planning, and considered restorative care.' },
  { slug: 'sophia-lee', name: 'Dr. Sophia Lee', role: 'Aligners & Aesthetic Dentistry', image: images.doctor3, intro: 'Helps patients understand their options and make confident, informed choices.' },
  { slug: 'daniel-wright', name: 'Dr. Daniel Wright', role: 'Family & Preventive Dentistry', image: images.doctor4, intro: 'Committed to making dental visits feel straightforward, respectful, and welcoming.' },
];

const iconPaths = {
  arrow: '<path d="M5 12h13M13 6l6 6-6 6"/>',
  arrowUp: '<path d="m6 15 6-6 6 6"/>',
  calendar: '<rect x="3" y="4.5" width="18" height="16" rx="2"/><path d="M16 2.5v4M8 2.5v4M3 9.5h18"/>',
  check: '<path d="m5 12 4 4L19 6"/>',
  clock: '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
  mail: '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
  phone: '<path d="M21 16.7v2.1a1.7 1.7 0 0 1-1.8 1.7 16.8 16.8 0 0 1-7.3-2.6 16.6 16.6 0 0 1-5.1-5.1 16.8 16.8 0 0 1-2.6-7.3A1.7 1.7 0 0 1 5.9 3.7H8a1.7 1.7 0 0 1 1.7 1.4 11 11 0 0 0 .6 2.4 1.7 1.7 0 0 1-.4 1.8L9 10.2a13.5 13.5 0 0 0 4.8 4.8l.9-.9a1.7 1.7 0 0 1 1.8-.4 11 11 0 0 0 2.4.6 1.7 1.7 0 0 1 1.4 1.7Z"/>',
  pin: '<path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/>',
  heart: '<path d="M20.8 8.7c0 5.4-8.8 10.6-8.8 10.6S3.2 14.1 3.2 8.7A4.5 4.5 0 0 1 12 6a4.5 4.5 0 0 1 8.8 2.7Z"/>',
  users: '<path d="M16 20v-1.5a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4V20M9 10a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM22 20v-1.5a4 4 0 0 0-3-3.9M16 2.2a4 4 0 0 1 0 7.7"/>',
  shield: '<path d="M12 21s8-3.8 8-10V5l-8-3-8 3v6c0 6.2 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/>',
  sparkle: '<path d="m12 3 1.3 4.7L18 9l-4.7 1.3L12 15l-1.3-4.7L6 9l4.7-1.3L12 3ZM19 15l.7 2.3L22 18l-2.3.7L19 21l-.7-2.3L16 18l2.3-.7L19 15Z"/>',
  menu: '<path d="M4 7h16M4 12h16M4 17h16"/>',
  close: '<path d="m6 6 12 12M18 6 6 18"/>',
  tooth: '<path d="M8.1 3.4C6.1 2.7 3.8 3.5 3 5.7c-1 2.6.3 5.2 1.3 7.5.8 1.9 1.2 4.1 2.2 5.5.7.9 1.8 1.2 2.4.1.7-1.2.6-4.1 2.9-4.1s2.2 2.9 2.9 4.1c.6 1.1 1.7.8 2.4-.1 1-1.4 1.4-3.6 2.2-5.5 1-2.3 2.3-4.9 1.3-7.5-.8-2.2-3.1-3-5.1-2.3L12 4.5 8.1 3.4Z"/>',
  diamond: '<path d="m3 9 4-5h10l4 5-9 11L3 9Z"/><path d="M3 9h18M12 20 8 9l4-5 4 5-4 11Z"/>',
  globe: '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/>',
  menuDots: '<circle cx="5" cy="12" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/>',
};

function icon(name, cls = '') {
  return `<svg class="icon ${cls}" viewBox="0 0 24 24" aria-hidden="true">${iconPaths[name] || iconPaths.sparkle}</svg>`;
}

function logo() {
  return `<a class="brand" href="#/" data-route="/" aria-label="Aurelia Dental Clinic home">
    <svg class="brand-mark" viewBox="0 0 48 48" aria-hidden="true"><path d="M14.3 5.8C11 4.6 7.2 5.9 5.8 9.4c-1.7 4.3.5 8.7 2.1 12.6 1.3 3.2 2 6.9 3.6 9.2 1.2 1.6 3 2 4 .2 1.1-2 1-6.9 4.5-6.9s3.4 4.9 4.5 6.9c1 1.8 2.8 1.4 4-.2 1.6-2.3 2.3-6 3.6-9.2 1.6-3.9 3.8-8.3 2.1-12.6-1.4-3.5-5.2-4.8-8.5-3.6L20 7.5l-5.7-1.7Z" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="M20 7.5 17.5 2M20 7.5 22.5 2" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
    <span class="brand-name">AURELIA<small>DENTAL CLINIC</small></span>
  </a>`;
}

function header(active = '/') {
  const nav = [
    ['/', 'Home'], ['/', 'About', 'about'], ['/treatments', 'Treatments'], ['/doctors', 'Doctors'], ['/reviews', 'Reviews'], ['/contact', 'Contact'],
  ];
  const activeRoute = active === '/' ? '/' : active.split('/')[1] ? `/${active.split('/')[1]}` : active;
  return `<div class="topbar"><div class="container topbar-inner"><span class="topbar-note">${icon('sparkle')} Thoughtful dental care, designed around you</span><span class="topbar-links"><span>Clinic details editable in admin</span><span>Patient support</span></span></div></div>
  <header class="site-header"><div class="container nav-inner">${logo()}<nav class="main-nav" id="main-nav" aria-label="Primary navigation">${nav.map(([route, label, scroll]) => `<a class="nav-link ${(scroll ? (activeRoute === '/' && location.hash.includes(scroll)) : activeRoute === route) ? 'active' : ''}" href="#/${route === '/' ? '' : route.slice(1)}" data-route="${route}" ${scroll ? `data-scroll="${scroll}"` : ''}>${label}${label === 'Treatments' ? ` ${icon('arrowUp', 'chevron')}` : ''}</a>`).join('')}</nav><div class="header-actions"><a class="btn btn-primary" href="#/book-appointment" data-route="/book-appointment">Book Appointment <span class="arrow">→</span></a><button class="menu-toggle" id="menu-toggle" aria-controls="main-nav" aria-expanded="false" aria-label="Open menu">${icon('menu')}</button></div></div></header>`;
}

function footer() {
  return `<footer class="footer"><div class="container footer-main"><div class="footer-brand">${logo()}<p>Confident smiles start here. A thoughtful, patient-first dental experience with clear information and straightforward next steps.</p><div class="footer-social" style="margin-top:18px"><a class="social" href="https://www.instagram.com/" target="_blank" rel="noreferrer" aria-label="Instagram">ig</a><a class="social" href="https://www.facebook.com/" target="_blank" rel="noreferrer" aria-label="Facebook">f</a><a class="social" href="https://www.linkedin.com/" target="_blank" rel="noreferrer" aria-label="LinkedIn">in</a></div></div><div><h4>Explore</h4><div class="footer-links"><a href="#/" data-route="/">Home</a><a href="#/treatments" data-route="/treatments">Treatments</a><a href="#/doctors" data-route="/doctors">Our doctors</a><a href="#/reviews" data-route="/reviews">Patient stories</a></div></div><div><h4>Visit</h4><div class="footer-links"><span>Clinic location to be confirmed</span><span>Opening hours configured in admin</span><a href="#/contact" data-route="/contact">Contact the clinic</a></div></div><div><h4>Get in touch</h4><div class="footer-links"><a href="tel:+18004522876">+1 (800) 452-2876</a><a href="mailto:hello@aureliadental.com">hello@aureliadental.com</a><a href="#/book-appointment" data-route="/book-appointment">Request an appointment</a></div></div></div><div class="container footer-bottom"><span>© 2026 Aurelia Dental Clinic. Demo content — replace with approved clinic details before launch.</span><span><a href="#/privacy" data-route="/privacy">Privacy</a> · <a href="#/terms" data-route="/terms">Terms</a></span></div></footer>`;
}

function page(content, active) {
  return `${header(active)}<main>${content}</main>${footer()}<button class="back-top" id="back-top" aria-label="Back to top">${icon('arrowUp')}</button>`;
}

function heroPage(eyebrow, title, text, photo = images.clinic, actions = '') {
  return `<section class="hero hero-page"><img class="hero-photo" src="${photo}" alt="Bright, modern dental clinic interior" /><div class="hero-content"><div class="hero-copy"><div class="breadcrumbs"><span>Home</span>${icon('arrow')}<span>${eyebrow}</span></div><p class="eyebrow light">${eyebrow}</p><h1>${title}</h1><p class="lede">${text}</p>${actions}</div></div></section>`;
}

function treatmentCard(t, catalog = false) {
  if (catalog) return `<article class="catalog-card"><img src="${t.image}" alt="${t.name}" loading="lazy" /><div class="catalog-body"><span class="catalog-label">${t.label}</span><h3>${t.name}</h3><p>${t.short}</p><a class="btn-link" href="#/treatments/${t.slug}" data-route="/treatments/${t.slug}">Explore treatment <span class="arrow">→</span></a></div></article>`;
  return `<article class="treatment-card"><img class="treatment-image" src="${t.image}" alt="${t.name}" loading="lazy" /><div class="treatment-card-body"><h3>${t.name}</h3><p>${t.short}</p><div class="treatment-meta"><span>${icon('clock')}${t.meta}</span></div><a class="btn-link" href="#/treatments/${t.slug}" data-route="/treatments/${t.slug}">Learn more <span class="arrow">→</span></a></div></article>`;
}

function doctorCard(d, wide = false) {
  return `<article class="doctor-card ${wide ? 'doctor-card--wide' : ''}"><img src="${d.image}" alt="Portrait of ${d.name}" loading="lazy" /><div class="doctor-card-body"><h3>${d.name}</h3><p>${d.role}</p><a class="btn btn-outline" href="#/doctors/${d.slug}" data-route="/doctors/${d.slug}">View profile <span class="arrow">→</span></a></div></article>`;
}

function reviewCard(name, location, text, demo = true) {
  return `<article class="review-card"><div class="stars" aria-label="Five stars">★★★★★</div><blockquote>“${text}”</blockquote><div class="review-byline"><span class="avatar">${name.split(' ').map(x => x[0]).join('').slice(0, 2)}</span><span><strong>${name}</strong><br />${location}</span></div>${demo ? '<span class="demo-tag">Demo content · approval required</span>' : ''}</article>`;
}

function homeView() {
  return `<section class="hero hero-home"><img class="hero-photo" src="${images.hero}" alt="Dentist speaking with a patient in a bright modern treatment room" /><div class="hero-content"><div class="hero-copy"><p class="eyebrow">Personalized dental care</p><h1>Confident Smiles<br />Start Here</h1><p class="lede">Modern dentistry with a thoughtful, patient-first approach. Explore dental care options and request a consultation with our team.</p><div class="hero-actions"><a class="btn btn-primary" href="#/book-appointment" data-route="/book-appointment">Book Appointment <span class="arrow">→</span></a><a class="btn btn-outline" href="#/treatments" data-route="/treatments">Explore Treatments</a></div><div class="hero-benefits"><span class="hero-benefit">${icon('check')}Clear treatment information</span><span class="hero-benefit">${icon('check')}Personalized consultations</span><span class="hero-benefit">${icon('check')}Comfortable environment</span></div></div></div></section>
  <section class="trust-strip"><div class="container trust-grid"><div class="trust-item"><span class="trust-icon">${icon('users')}</span><span><strong class="trust-title">Patient-first</strong><br /><span class="trust-copy">A calm, respectful experience</span></span></div><div class="trust-item"><span class="trust-icon">${icon('tooth')}</span><span><strong class="trust-title">Clear information</strong><br /><span class="trust-copy">Know what to expect</span></span></div><div class="trust-item"><span class="trust-icon">${icon('diamond')}</span><span><strong class="trust-title">Thoughtful care</strong><br /><span class="trust-copy">Options explained clearly</span></span></div><div class="trust-item"><span class="trust-icon">${icon('calendar')}</span><span><strong class="trust-title">Easy requests</strong><br /><span class="trust-copy">Start with a simple enquiry</span></span></div></div></section>
  <section class="section" id="treatments"><div class="container"><div class="section-head"><div><p class="eyebrow">Our treatments</p><h2>Premium dental care for every smile</h2></div><a class="btn-link" href="#/treatments" data-route="/treatments">View all treatments <span class="arrow">→</span></a></div><div class="treatment-grid">${treatments.slice(0, 4).map(t => treatmentCard(t)).join('')}</div></div></section>
  <section class="section section-cream" id="about"><div class="container"><div class="about-grid"><div class="about-image"><img src="${images.reception}" alt="Welcoming dental clinic reception" loading="lazy" /></div><div class="about-copy"><p class="eyebrow">About Aurelia</p><h2>Why choose Aurelia Dental Clinic?</h2><p>We combine careful clinical thinking with a warm, modern experience. Our team is here to help you understand your options, ask better questions, and take the next step at a pace that feels right.</p><div class="value-list"><span class="value-item">${icon('heart')}Patient-focused care</span><span class="value-item">${icon('check')}Clear treatment planning</span><span class="value-item">${icon('sparkle')}Modern equipment</span><span class="value-item">${icon('users')}A comfortable environment</span></div><a class="btn btn-primary" href="#/contact" data-route="/contact">Learn more about us <span class="arrow">→</span></a></div></div></div></section>
  <section class="section" id="doctors"><div class="container"><div class="section-head"><div><p class="eyebrow">Meet our doctors</p><h2>Expert care in trusted hands</h2></div><a class="btn-link" href="#/doctors" data-route="/doctors">View all doctors <span class="arrow">→</span></a></div><div class="doctor-grid">${doctors.slice(0, 3).map(d => doctorCard(d)).join('')}</div></div></section>
  <section class="section section-cream" id="reviews"><div class="container"><div class="section-head"><div><p class="eyebrow">Patient stories</p><h2>Real smiles. Real people.</h2></div><a class="btn-link" href="#/reviews" data-route="/reviews">View patient stories <span class="arrow">→</span></a></div><div class="review-grid">${reviewCard('Sarah T.', 'Sample location', 'This space is reserved for an approved patient story. We believe trust should be earned, not invented.')} ${reviewCard('Your story', 'Share your experience', 'Patient feedback will appear here after written permission and review.')} ${reviewCard('Aurelia team', 'Listening first', 'Every question helps us make the experience clearer for the next patient.')}</div></div></section>
  <section class="cta-band"><div class="container cta-inner"><div><p class="eyebrow light">Your healthier smile starts here</p><h2>Book your appointment today</h2><p>Tell us what you are looking for and our team will get back to you with clear next steps.</p></div><a class="btn btn-primary" href="#/book-appointment" data-route="/book-appointment">Request an appointment <span class="arrow">→</span></a></div></section>`;
}

function treatmentsView() {
  return `${heroPage('Treatments', 'Advanced care for healthier, brighter smiles', 'Explore dental treatment options and learn what to expect from consultation through ongoing care.', images.consultation, '<div class="hero-actions"><a class="btn btn-primary" href="#/book-appointment" data-route="/book-appointment">Book a consultation <span class="arrow">→</span></a><a class="btn btn-outline" href="#/contact" data-route="/contact">Speak with our team</a></div>')}<section class="section"><div class="container"><div class="section-head"><div><p class="eyebrow">Our treatments</p><h2>Thoughtful options, clearly explained</h2><p class="section-intro">Every treatment begins with a conversation and a clinical assessment. Treatment suitability, timing, cost, risks, and alternatives are discussed with you before you decide.</p></div></div><div class="catalog-grid">${treatments.map(t => treatmentCard(t, true)).join('')}</div></div></section><section class="section section-cream"><div class="container"><div class="approach-grid"><div class="approach-intro"><p class="eyebrow">The Aurelia approach</p><h2>Modern dentistry.<br />A more personal experience.</h2><p>We make space for questions and keep the next step simple, so you can make informed decisions about your care.</p></div><div class="approach-item">${icon('sparkle', 'icon-lg')}<h3>Clear plans</h3><p>Understand what happens next.</p></div><div class="approach-item">${icon('heart', 'icon-lg')}<h3>Gentle care</h3><p>Respectful, comfort-minded visits.</p></div><div class="approach-item">${icon('calendar', 'icon-lg')}<h3>Easy requests</h3><p>Start with a simple enquiry.</p></div><div class="approach-item">${icon('shield', 'icon-lg')}<h3>Privacy aware</h3><p>We ask only what we need.</p></div></div></div></section><section class="section"><div class="container"><div class="section-head"><div><p class="eyebrow">Meet the team</p><h2>Care that begins with listening</h2></div><a class="btn-link" href="#/doctors" data-route="/doctors">Meet all doctors <span class="arrow">→</span></a></div><div class="doctor-grid">${doctors.slice(0, 3).map(d => doctorCard(d)).join('')}</div></div></section><section class="cta-band"><div class="container cta-inner"><div><p class="eyebrow light">Have questions?</p><h2>Start with a conversation</h2><p>Share what you would like help with. The team will respond with the appropriate next step.</p></div><a class="btn btn-primary" href="#/book-appointment" data-route="/book-appointment">Book a consultation <span class="arrow">→</span></a></div></section>`;
}

function treatmentDetailView(slug) {
  const treatment = treatments.find(t => t.slug === slug) || treatments[0];
  return `${heroPage('Treatment detail', treatment.name, treatment.short, treatment.image, '<div class="hero-actions"><a class="btn btn-primary" href="#/book-appointment" data-route="/book-appointment">Book a consultation <span class="arrow">→</span></a><a class="btn btn-outline" href="#/treatments" data-route="/treatments">Back to treatments</a></div>')}<section class="section"><div class="container"><div class="detail-grid"><div class="detail-image"><img src="${treatment.image}" alt="${treatment.name}" /></div><div class="detail-copy"><p class="eyebrow">What to expect</p><h2>A clear plan, built around you</h2><p>${treatment.details}</p><ul class="detail-list"><li>${icon('check')}Assessment and conversation before treatment</li><li>${icon('check')}Options, limitations, and alternatives explained</li><li>${icon('check')}Follow-up guidance tailored to your plan</li></ul><div class="notice">This page provides general information only. A qualified dental professional must assess your individual circumstances before any treatment decision.</div><a class="btn btn-primary" style="margin-top:24px" href="#/book-appointment" data-route="/book-appointment">Request an appointment <span class="arrow">→</span></a></div></div><div class="detail-sections"><div class="detail-section"><h3>How the process works</h3><p>We start by understanding your goals and reviewing your oral health. Your clinician will outline the recommended next steps, answer questions, and make sure you understand the choices available to you.</p></div><div class="detail-section"><h3>Benefits and limitations</h3><p>Potential benefits depend on your starting point and treatment plan. We will also talk through risks, maintenance, longevity, and what the treatment cannot change.</p></div><div class="detail-section"><h3>Questions to bring to your consultation</h3><p>Ask about suitability, alternatives, timing, aftercare, fees, and who to contact if you have concerns after your appointment.</p></div></div></div></section><section class="section section-cream"><div class="container"><div class="section-head"><div><p class="eyebrow">You may also like</p><h2>Explore related care</h2></div></div><div class="catalog-grid">${treatments.filter(t => t.slug !== treatment.slug).slice(0, 3).map(t => treatmentCard(t, true)).join('')}</div></div></section>`;
}

function doctorsView() {
  return `${heroPage('Our doctors', 'Care that begins with listening', 'Meet the team behind the Aurelia experience. Public profile details should be verified by the clinic before launch.', images.reception, '<div class="hero-actions"><a class="btn btn-primary" href="#/book-appointment" data-route="/book-appointment">Request an appointment <span class="arrow">→</span></a></div>')}<section class="section"><div class="container"><div class="section-head"><div><p class="eyebrow">Meet our specialists</p><h2>A thoughtful team for every stage of care</h2><p class="section-intro">These are demonstration profiles for the interface. Add verified credentials, registrations, languages, and locations in the admin system before publishing.</p></div></div><div class="doctor-grid">${doctors.map(d => doctorCard(d, true)).join('')}</div></div></section><section class="section section-cream"><div class="container"><div class="about-grid"><div class="about-image"><img src="${images.consultation}" alt="Dentist consulting with a patient" loading="lazy" /></div><div class="about-copy"><p class="eyebrow">A better conversation</p><h2>Good care is easier to choose when it is clearly explained.</h2><p>Bring your questions. We will help you understand the available options and what a sensible next step looks like for you.</p><a class="btn btn-primary" href="#/book-appointment" data-route="/book-appointment">Talk with our team <span class="arrow">→</span></a></div></div></div></section>`;
}

function doctorDetailView(slug) {
  const doctor = doctors.find(d => d.slug === slug) || doctors[0];
  return `${heroPage('Doctor profile', doctor.name, doctor.role, doctor.image, '<div class="hero-actions"><a class="btn btn-primary" href="#/book-appointment" data-route="/book-appointment">Request an appointment <span class="arrow">→</span></a><a class="btn btn-outline" href="#/doctors" data-route="/doctors">Meet the team</a></div>')}<section class="section"><div class="container"><div class="detail-grid"><div class="detail-image"><img src="${doctor.image}" alt="Portrait of ${doctor.name}" /></div><div class="detail-copy"><p class="eyebrow">Demo profile · verification required</p><h2>A calm, considered approach</h2><p>${doctor.intro} This profile is a presentation example and should be replaced with verified biography, qualifications, registration details, languages, and location before publication.</p><ul class="detail-list"><li>${icon('check')}Areas of practice configured by the clinic</li><li>${icon('check')}Appointment eligibility set in admin</li><li>${icon('check')}Credentials and registration to be verified</li></ul><a class="btn btn-primary" href="#/book-appointment" data-route="/book-appointment">Request an appointment <span class="arrow">→</span></a></div></div></div></section>`;
}

function reviewsView() {
  const cards = [
    reviewCard('Patient story', 'Approval pending', 'Approved patient feedback will appear here after written permission and moderation.', true),
    reviewCard('Patient story', 'Approval pending', 'We do not publish invented reviews, ratings, or outcomes.', true),
    reviewCard('Patient story', 'Approval pending', 'Your experience can help another patient feel more informed.', true),
    reviewCard('Patient story', 'Approval pending', 'This space is ready for a real, permissioned story.', true),
  ];
  return `${heroPage('Reviews & contact', 'Trusted by patients. Ready to welcome you.', 'Experience compassionate care, clear information, and a simple way to start a conversation with the Aurelia team.', images.consultation, '<div class="hero-actions"><a class="btn btn-primary" href="#/book-appointment" data-route="/book-appointment">Book an appointment <span class="arrow">→</span></a><a class="btn btn-outline" href="#/contact" data-route="/contact">Get in touch</a></div>')}<section class="rating-band"><div class="container rating-grid"><div class="rating-item"><div><div class="rating-stars">★★★★★</div><strong class="rating-value">—</strong></div><span class="rating-label">Average rating<br />Awaiting verified data</span></div><div class="rating-item"><span class="trust-icon">${icon('users')}</span><span class="rating-label"><strong class="rating-value" style="font-size:21px">—</strong><br />No public patient count</span></div><div class="rating-item"><span class="trust-icon">${icon('heart')}</span><span class="rating-label"><strong class="rating-value" style="font-size:21px">—</strong><br />No unverified claims</span></div><div class="rating-item"><span class="trust-icon">${icon('globe')}</span><span class="rating-label">Clinic location<br />To be configured</span></div></div></section><section class="section"><div class="container"><div class="review-toolbar"><div><p class="eyebrow">Patient stories</p><h2>Real stories. Real smiles.</h2></div><div class="tabs"><button class="tab active" data-review-filter="all">All stories</button><button class="tab" data-review-filter="approved">Approved only</button></div></div><div class="review-grid review-grid--four" id="review-list">${cards.join('')}</div></div></section>${contactAndForm()}`;
}

function contactAndForm() {
  return `<section class="section section-cream" id="contact"><div class="container content-grid"><div class="panel"><p class="eyebrow">Get in touch</p><h2>We are here to help</h2><p class="section-intro">Use the details below for general enquiries, or send an appointment request and the team will follow up.</p><div class="contact-items"><div class="contact-item">${icon('phone')}<span><strong>+1 (800) 452-2876</strong><span>Patient support</span></span></div><div class="contact-item">${icon('mail')}<span><strong>hello@aureliadental.com</strong><span>General enquiries</span></span></div><div class="contact-item">${icon('pin')}<span><strong>Clinic details pending</strong><span>Location configured in admin</span></span></div></div><div class="hours-map"><div class="hours"><h3>Opening hours</h3><table><tr><td>Monday – Friday</td><td>Configured in admin</td></tr><tr><td>Saturday</td><td>Configured in admin</td></tr><tr><td>Sunday</td><td>Closed</td></tr></table></div><div class="map-card"><div class="map-label">${icon('pin')}Aurelia Dental</div><span class="btn btn-outline">Directions unavailable</span></div></div></div>${bookingForm()}</div></section>`;
}

function bookingForm() {
  return `<div class="panel" id="booking-form"><p class="eyebrow">Book your appointment</p><h2>Start with a simple request</h2><p class="section-intro">Share only what is needed for an initial enquiry. This demo does not collect medical history.</p><form data-booking-form novalidate><div class="form-grid"><div class="field"><label for="full-name">Full name <span>*</span></label><input id="full-name" name="fullName" autocomplete="name" placeholder="Enter your full name" required /></div><div class="field"><label for="email">Email address <span>*</span></label><input id="email" type="email" name="email" autocomplete="email" placeholder="you@example.com" required /></div><div class="field"><label for="phone">Phone number <span>*</span></label><input id="phone" type="tel" name="phone" autocomplete="tel" placeholder="+1 (000) 000-0000" required /></div><div class="field"><label for="location">Preferred location</label><select id="location" name="location"><option value="">Select a location</option><option>Clinic location to be confirmed</option></select></div><div class="field"><label for="date">Preferred date <span>*</span></label><input id="date" type="date" name="date" required /></div><div class="field"><label for="interest">Treatment interest</label><select id="interest" name="interest"><option value="">Select a treatment</option>${treatments.map(t => `<option>${t.name}</option>`).join('')}</select></div><div class="field field-full"><label for="message">Message <small>(optional)</small></label><textarea id="message" name="message" placeholder="Tell us how we can help you..."></textarea></div><label class="checkbox-field"><input type="checkbox" name="privacy" required /><span>I understand this is an appointment request, not a confirmed booking, and agree to be contacted about my enquiry. <span>*</span></span></label><div class="field-full"><button class="btn btn-primary" type="submit">Request appointment <span class="arrow">→</span></button><p class="form-note">A team member would normally follow up to discuss availability. No appointment is confirmed by this form.</p><div class="form-message" data-form-message hidden></div></div></div></form></div>`;
}

function contactView() {
  return `${heroPage('Contact', 'A better experience starts with a conversation', 'Reach out with a general question or request an appointment. Clinic contact details can be configured before launch.', images.reception, '<div class="hero-actions"><a class="btn btn-primary" href="#/book-appointment" data-route="/book-appointment">Book an appointment <span class="arrow">→</span></a></div>')}${contactAndForm()}`;
}

function bookView() {
  return `${heroPage('Book appointment', 'Let’s find the right next step', 'Send an appointment request and the Aurelia team will follow up. Your preferred date is a request only until the clinic confirms availability.', images.hero, '')}<section class="section section-cream"><div class="container" style="max-width:920px">${bookingForm()}</div></section>`;
}

function legalView(kind) {
  const title = kind === 'privacy' ? 'Privacy notice' : kind === 'terms' ? 'Terms of use' : 'Accessibility';
  return `${heroPage('Information', title, 'This demo page is a placeholder for the clinic-approved policy text.', images.clinic)}<section class="section"><div class="container" style="max-width:840px"><div class="detail-sections"><div class="detail-section"><h3>Demo content notice</h3><p>Replace this placeholder with reviewed, jurisdiction-specific content before collecting real patient information. The public booking form is designed to request only contact and appointment preference information.</p></div><div class="detail-section"><h3>What this project does not claim</h3><p>This interface does not claim regulatory compliance, clinical outcomes, verified credentials, verified reviews, or confirmed appointment availability.</p></div></div></div></section>`;
}

function routeFromHash() {
  const raw = location.hash.replace(/^#/, '') || '/';
  const clean = raw.split('#')[0] || '/';
  return clean.startsWith('/') ? clean : `/${clean}`;
}

function render() {
  const route = routeFromHash();
  let content = '';
  if (route === '/' || route === '/home') content = homeView();
  else if (route === '/treatments') content = treatmentsView();
  else if (route.startsWith('/treatments/')) content = treatmentDetailView(route.split('/')[2]);
  else if (route === '/doctors') content = doctorsView();
  else if (route.startsWith('/doctors/')) content = doctorDetailView(route.split('/')[2]);
  else if (route === '/reviews') content = reviewsView();
  else if (route === '/contact') content = contactView();
  else if (route === '/book-appointment') content = bookView();
  else if (route === '/privacy' || route === '/terms' || route === '/accessibility') content = legalView(route.slice(1));
  else content = homeView();
  app.innerHTML = page(content, route);
  bindEvents();
  requestAnimationFrame(() => {
    const anchor = location.hash.split('#')[2];
    if (anchor) document.getElementById(anchor)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    else window.scrollTo({ top: 0, behavior: 'auto' });
  });
}

function go(route, scroll) {
  const target = route === '/' ? '#/' : `#${route}`;
  if (scroll) {
    history.pushState({}, '', `${target}#${scroll}`);
    render();
  } else if (location.hash !== target) location.hash = target.slice(1);
  else render();
}

function bindEvents() {
  document.querySelectorAll('[data-route]').forEach(link => {
    link.addEventListener('click', event => {
      event.preventDefault();
      const route = link.dataset.route || '/';
      const scroll = link.dataset.scroll;
      document.querySelector('.main-nav')?.classList.remove('open');
      document.body.classList.remove('menu-open');
      go(route, scroll);
    });
  });
  const menuToggle = document.querySelector('#menu-toggle');
  const mainNav = document.querySelector('#main-nav');
  menuToggle?.addEventListener('click', () => {
    const open = mainNav.classList.toggle('open');
    menuToggle.setAttribute('aria-expanded', String(open));
    menuToggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
    menuToggle.innerHTML = icon(open ? 'close' : 'menu');
    document.body.classList.toggle('menu-open', open);
  });
  document.querySelectorAll('[data-review-filter]').forEach(button => button.addEventListener('click', () => {
    document.querySelectorAll('[data-review-filter]').forEach(item => item.classList.toggle('active', item === button));
    const list = document.querySelector('#review-list');
    if (list && button.dataset.reviewFilter === 'approved') list.innerHTML = '<div class="notice" style="grid-column:1/-1">No approved public patient stories have been added yet. This demo keeps the empty state truthful.</div>';
  }));
  document.querySelectorAll('form[data-booking-form]').forEach(form => form.addEventListener('submit', handleBookingSubmit));
  const top = document.querySelector('#back-top');
  window.addEventListener('scroll', () => top?.classList.toggle('visible', window.scrollY > 500), { passive: true });
  top?.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
}

function handleBookingSubmit(event) {
  event.preventDefault();
  const form = event.currentTarget;
  const message = form.querySelector('[data-form-message]');
  const data = new FormData(form);
  if (!data.get('fullName') || !data.get('email') || !data.get('phone') || !data.get('date') || !data.get('privacy')) {
    message.hidden = false;
    message.className = 'form-message error';
    message.textContent = 'Please complete the required fields and privacy acknowledgement before sending.';
    return;
  }
  const reference = `AUR-${new Date().getFullYear()}-${Math.random().toString(36).slice(2, 8).toUpperCase()}`;
  const request = Object.fromEntries(data.entries());
  request.reference = reference;
  request.createdAt = new Date().toISOString();
  const existing = JSON.parse(localStorage.getItem('aurelia_demo_requests') || '[]');
  existing.push(request);
  localStorage.setItem('aurelia_demo_requests', JSON.stringify(existing));
  message.hidden = false;
  message.className = 'form-message success';
  message.innerHTML = `<strong>Request received in this browser demo.</strong><br />Reference ${reference}. A clinic team would follow up once a secure backend and real clinic contact details are connected. This is not a confirmed appointment.`;
  form.reset();
}

window.addEventListener('hashchange', render);
window.addEventListener('popstate', render);
render();
