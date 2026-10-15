document.addEventListener('DOMContentLoaded', () => {
  const $ = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => [...r.querySelectorAll(s)];

  /* Sidebar (mobile drawer) */
  const sidebar = $('#sidebar'), scrim = $('#scrim');
  const toggleSidebar = (open) => { sidebar?.classList.toggle('open', open); scrim?.classList.toggle('show', open); };
  $('[data-sidebar-toggle]')?.addEventListener('click', () => toggleSidebar(!sidebar.classList.contains('open')));
  scrim?.addEventListener('click', () => toggleSidebar(false));
  $$('#sidebar a').forEach((a) => a.addEventListener('click', () => { if (window.innerWidth <= 900) toggleSidebar(false); }));

  /* Sidebar folding (desktop hover-expand) */
  let hoverLock = false;

  const updateSidebarFold = (folded) => {
    sidebar?.classList.remove('is-hovered');
    document.documentElement.classList.toggle('sidebar-folded', folded);
    $('.app')?.classList.toggle('sidebar-folded', folded);
    try { localStorage.setItem('banlic_sidebar_folded', folded ? '1' : '0'); } catch(e) {}
  };

  // Sync initial state if head added it
  if (document.documentElement.classList.contains('sidebar-folded')) {
    $('.app')?.classList.add('sidebar-folded');
  }

  // Fold toggle click handlers
  $$('[data-sidebar-fold]').forEach((btn) => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      const willBeFolded = !document.documentElement.classList.contains('sidebar-folded');
      hoverLock = true;
      updateSidebarFold(willBeFolded);
      setTimeout(() => { hoverLock = false; }, 400);
    });
  });

  // Hover to fold out (expand) when sidebar is folded on desktop
  if (sidebar) {
    sidebar.addEventListener('mouseenter', () => {
      if (hoverLock || window.innerWidth <= 900) return;
      if (document.documentElement.classList.contains('sidebar-folded')) {
        sidebar.classList.add('is-hovered');
      }
    });

    sidebar.addEventListener('mouseleave', () => {
      sidebar.classList.remove('is-hovered');
      hoverLock = false;
    });
  }

  /* Theme Toggle (Dark / Light mode) */
  const updateChartThemes = (isDark) => {
    if (window.Chart) {
      Chart.defaults.color = isDark ? '#95a0c6' : '#6b7394';
      Chart.defaults.borderColor = isDark ? '#222b4c' : '#e3e7f3';
      if (Chart.instances) {
        Object.values(Chart.instances).forEach((chart) => {
          if (chart && typeof chart.update === 'function') {
            if (chart.options.scales?.x?.ticks) chart.options.scales.x.ticks.color = isDark ? '#95a0c6' : '#6b7394';
            if (chart.options.scales?.y?.ticks) chart.options.scales.y.ticks.color = isDark ? '#95a0c6' : '#6b7394';
            if (chart.options.scales?.x?.grid) chart.options.scales.x.grid.color = isDark ? '#222b4c' : '#e3e7f3';
            if (chart.options.scales?.y?.grid) chart.options.scales.y.grid.color = isDark ? '#222b4c' : '#e3e7f3';
            chart.update();
          }
        });
      }
    }
  };

  const applyTheme = (theme) => {
    const isDark = theme === 'dark';
    document.documentElement.classList.toggle('dark', isDark);
    try { localStorage.setItem('banlic_theme', theme); } catch (e) {}
    $$('.theme-label-mode').forEach((el) => el.textContent = isDark ? 'Dark' : 'Light');
    updateChartThemes(isDark);
  };

  // Sync initial labels and charts
  const initialIsDark = document.documentElement.classList.contains('dark');
  $$('.theme-label-mode').forEach((el) => el.textContent = initialIsDark ? 'Dark' : 'Light');
  if (initialIsDark) updateChartThemes(true);

  $$('[data-theme-toggle]').forEach((btn) => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const current = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
      applyTheme(current === 'dark' ? 'light' : 'dark');
    });
  });

  /* Public navbar (mobile) */
  const pubnav = $('#pubnav');
  const navToggleBtn = $('[data-nav-toggle]');
  navToggleBtn?.addEventListener('click', (e) => {
    e.stopPropagation();
    const isOpen = pubnav?.classList.toggle('open');
    navToggleBtn.classList.toggle('open', isOpen);
  });
  $$('#pubnav a').forEach((a) => a.addEventListener('click', () => {
    pubnav?.classList.remove('open');
    navToggleBtn?.classList.remove('open');
  }));
  document.addEventListener('click', (e) => {
    if (pubnav?.classList.contains('open') && !e.target.closest('#pubnav') && !e.target.closest('[data-nav-toggle]')) {
      pubnav.classList.remove('open');
      navToggleBtn?.classList.remove('open');
    }
  });

  /* Virtual Keyboard auto-adjust for mobile devices only */
  const isMobileDevice = () => window.innerWidth <= 768 || ('ontouchstart' in window && window.innerWidth <= 900);

  if (window.visualViewport) {
    const onViewportResize = () => {
      if (!isMobileDevice()) {
        document.body.classList.remove('keyboard-open');
        return;
      }
      const isShort = window.visualViewport.height < window.innerHeight * 0.78;
      document.body.classList.toggle('keyboard-open', isShort);
    };
    window.visualViewport.addEventListener('resize', onViewportResize);
  }

  // Auto-scroll input into optimal view on mobile focus
  $$('.auth-card input').forEach((input) => {
    input.addEventListener('focus', () => {
      if (!isMobileDevice()) return;
      document.body.classList.add('keyboard-open');
      setTimeout(() => {
        input.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }, 250);
    });
    input.addEventListener('blur', () => {
      if (!isMobileDevice()) {
        document.body.classList.remove('keyboard-open');
        return;
      }
      setTimeout(() => {
        if (!document.activeElement || !document.activeElement.matches('.auth-card input')) {
          if (!window.visualViewport || window.visualViewport.height >= window.innerHeight * 0.8) {
            document.body.classList.remove('keyboard-open');
          }
        }
      }, 200);
    });
  });

  /* Dropdowns (notifications, user menu) */
  $$('[data-dropdown]').forEach((dd) => {
    $('[data-dropdown-btn]', dd).addEventListener('click', (e) => {
      e.stopPropagation();
      const wasOpen = dd.classList.contains('open');
      $$('[data-dropdown]').forEach((x) => x.classList.remove('open'));
      dd.classList.toggle('open', !wasOpen);
    });
  });
  document.addEventListener('click', (e) => {
    if (!e.target.closest('[data-dropdown]')) $$('[data-dropdown]').forEach((x) => x.classList.remove('open'));
  });

  /* Show / hide password */
  $$('[data-toggle-password]').forEach((btn) => btn.addEventListener('click', () => {
    const input = btn.previousElementSibling;
    input.type = input.type === 'password' ? 'text' : 'password';
    btn.textContent = input.type === 'password' ? 'Show' : 'Hide';
  }));

  /* Auto-hide success alerts */
  $$('.alert-success[data-autohide]').forEach((a) => setTimeout(() => { a.style.opacity = 0; setTimeout(() => a.remove(), 400); }, 6000));

  /* Confirmation modal: add data-confirm="message" to any button inside a form.
     Optional: data-confirm-title, data-confirm-ok, data-confirm-type="danger". */
  const modal = $('#confirmModal');
  if (modal) {
    let pendingForm = null;
    const okBtn = $('[data-modal-ok]', modal);
    const close = () => { modal.classList.remove('show'); pendingForm = null; };

    $$('[data-confirm]').forEach((el) => el.addEventListener('click', (e) => {
      const form = el.closest('form');
      if (!form) return;
      if (!form.reportValidity()) return;      // let the browser show validation first
      e.preventDefault();
      pendingForm = form;
      $('[data-modal-title]', modal).textContent = el.dataset.confirmTitle || 'Are you sure?';
      $('[data-modal-text]', modal).textContent = el.dataset.confirm;
      okBtn.textContent = el.dataset.confirmOk || 'Confirm';
      okBtn.className = 'btn ' + (el.dataset.confirmType === 'danger' ? 'btn-danger' : 'btn-primary');
      modal.classList.add('show');
      okBtn.focus();
    }));
    $('[data-modal-cancel]', modal).addEventListener('click', close);
    okBtn.addEventListener('click', () => { if (pendingForm) { okBtn.disabled = true; pendingForm.submit(); } });
  }

  /* Universal modal dismiss (Escape key and backdrop click) for all modals */
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      $$('.modal.show').forEach((m) => {
        m.classList.remove('show');
        if (m.id === 'doc-preview-modal') {
          const c = $('#doc-preview-container');
          if (c) c.innerHTML = '';
        }
      });
    }
  });

  document.addEventListener('click', (e) => {
    if (e.target.classList && e.target.classList.contains('modal') && e.target.classList.contains('show')) {
      e.target.classList.remove('show');
      if (e.target.id === 'doc-preview-modal') {
        const c = $('#doc-preview-container');
        if (c) c.innerHTML = '';
      }
    }
  });

  /* File upload widget (reusable: <x-file-upload name="evidence" />) */
  $$('[data-file-upload]').forEach((w) => {
    const input = $('input[type=file]', w), list = $('[data-file-list]', w), err = $('[data-file-error]', w), zone = $('[data-dropzone]', w);
    const max = +w.dataset.max || 5, maxMB = +w.dataset.maxSize || 5;
    const types = (w.dataset.types || '').split(',').map((t) => t.trim().toLowerCase());
    let files = [];

    const size = (b) => (b >= 1048576 ? (b / 1048576).toFixed(1) + ' MB' : Math.max(1, Math.round(b / 1024)) + ' KB');
    const sync = () => {
      const dt = new DataTransfer();
      files.forEach((f) => dt.items.add(f));
      input.files = dt.files;
      render();
    };
    const render = () => {
      list.replaceChildren();
      files.forEach((f, i) => {
        const li = document.createElement('li'); li.className = 'file-item';
        let thumb;
        if (f.type.startsWith('image/')) { thumb = document.createElement('img'); thumb.src = URL.createObjectURL(f); thumb.alt = ''; }
        else { thumb = document.createElement('div'); thumb.className = 'ph'; thumb.textContent = f.name.split('.').pop().toUpperCase(); }
        const meta = document.createElement('div'); meta.className = 'meta';
        const name = document.createElement('b'); name.textContent = f.name;
        const sz = document.createElement('small'); sz.textContent = size(f.size);
        meta.append(name, sz);
        const rm = document.createElement('button'); rm.type = 'button'; rm.innerHTML = '&times;'; rm.setAttribute('aria-label', 'Remove ' + f.name);
        rm.addEventListener('click', () => { files.splice(i, 1); err.textContent = ''; sync(); });
        li.append(thumb, meta, rm); list.append(li);
      });
    };
    const add = (incoming) => {
      err.textContent = '';
      for (const f of incoming) {
        const ext = f.name.split('.').pop().toLowerCase();
        if (!types.includes(ext)) { err.textContent = `"${f.name}" is not an allowed file type.`; continue; }
        if (f.size > maxMB * 1048576) { err.textContent = `"${f.name}" is larger than ${maxMB} MB.`; continue; }
        if (files.length >= max) { err.textContent = `You can attach up to ${max} files.`; break; }
        if (!files.some((x) => x.name === f.name && x.size === f.size)) files.push(f);
      }
      sync();
    };
    zone.addEventListener('click', () => input.click());
    zone.addEventListener('keydown', (e) => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); input.click(); } });
    input.addEventListener('change', () => add([...input.files]));
    ['dragenter', 'dragover'].forEach((ev) => zone.addEventListener(ev, (e) => { e.preventDefault(); zone.classList.add('drag'); }));
    ['dragleave', 'drop'].forEach((ev) => zone.addEventListener(ev, (e) => { e.preventDefault(); zone.classList.remove('drag'); }));
    zone.addEventListener('drop', (e) => add([...e.dataTransfer.files]));
  });

  /* Prevent browser Back button on main dashboard from returning to login/guest pages */
  const currentPath = window.location.pathname.replace(/\/+$/, '');
  const isMainDashboard = currentPath === '/dashboard' || currentPath === '/admin/dashboard';
  if (isMainDashboard && window.history && window.history.pushState) {
    window.history.pushState(null, '', window.location.href);
    window.addEventListener('popstate', () => {
      window.history.pushState(null, '', window.location.href);
    });
  }
});

/* Reload immediately when restored from browser bfcache (Back-Forward Cache) to revalidate session */
window.addEventListener('pageshow', (event) => {
  if (event.persisted) {
    window.location.reload();
  }
});
