/**
 * App Calendar - Responsive Version
 */

'use strict';

document.addEventListener('DOMContentLoaded', function () {
  const direction = isRtl ? 'rtl' : 'ltr';
  
  (function () {
    // DOM Elements
    const calendarEl = document.getElementById('calendar');
    const appCalendarSidebar = document.querySelector('.app-calendar-sidebar');
    const addEventSidebar = document.getElementById('addEventSidebar');
    const appOverlay = document.querySelector('.app-overlay');
    const offcanvasTitle = document.querySelector('.offcanvas-title');
    const btnToggleSidebar = document.querySelector('.btn-toggle-sidebar');
    const btnSubmit = document.getElementById('addEventBtn');
    const btnDeleteEvent = document.querySelector('.btn-delete-event');
    const btnCancel = document.querySelector('.btn-cancel');
    const eventTitle = document.getElementById('event_name');
    const eventStartDate = document.getElementById('event_date');
    const eventUrl = document.getElementById('image_url');
    const eventLocation = document.getElementById('location_url');
    const eventDescription = document.getElementById('venu_cntact');
    const allDaySwitch = document.querySelector('.allDay-switch');
    const selectAll = document.querySelector('.select-all');
    const filterInputs = Array.from(document.querySelectorAll('.input-filter'));
    const inlineCalendar = document.querySelector('.inline-calendar');

    // Calendar settings - Updated to match your statuses
    const calendarColors = {
      pending: 'warning',
      completed: 'success',
      cancelled: 'danger',
      cancelled: 'danger',
      confirmed: 'primary',
      ongoing: 'info',
      default: 'secondary'
    };

    // External jQuery Elements
    const eventLabel = $('#eventLabel');
    const eventGuests = $('#eventGuests');

    // Event Data
    let currentEvents = [];
    let isFormValid = false;
    let eventToUpdate = null;
    let inlineCalInstance = null;

    // Offcanvas Instance
    const bsAddEventSidebar = new bootstrap.Offcanvas(addEventSidebar);

    // Initialize Select2 with responsive settings
    if (eventLabel.length) {
      function renderBadges(option) {
        if (!option.id) {
          return option.text;
        }
        var $badge = "<span class='badge badge-dot bg-" + $(option.element).data('label') + " me-2'> " + '</span>' + option.text;
        return $badge;
      }
      eventLabel.wrap('<div class="position-relative"></div>').select2({
        placeholder: 'Select value',
        dropdownParent: eventLabel.parent(),
        templateResult: renderBadges,
        templateSelection: renderBadges,
        minimumResultsForSearch: -1,
        width: '100%', // Make it responsive
        escapeMarkup: function (es) {
          return es;
        }
      });
    }

    // Render guest avatars
    if (eventGuests.length) {
      function renderGuestAvatar(option) {
        if (!option.id) return option.text;
        return `
          <div class='d-flex flex-wrap align-items-center'>
            <div class='avatar avatar-xs me-2'>
              <img src='${assetsPath}img/avatars/${$(option.element).data('avatar')}'
                alt='avatar' class='rounded-circle' />
            </div>
            ${option.text}
          </div>`;
      }
      eventGuests.wrap('<div class="position-relative"></div>').select2({
        placeholder: 'Select value',
        dropdownParent: eventGuests.parent(),
        closeOnSelect: false,
        templateResult: renderGuestAvatar,
        templateSelection: renderGuestAvatar,
        width: '100%', // Make it responsive
        escapeMarkup: function (es) {
          return es;
        }
      });
    }

    // Event start (flatpicker)
    let start = null;
    if (eventStartDate) {
      start = eventStartDate.flatpickr({
        monthSelectorType: 'static',
        static: true,
        enableTime: true,
        altFormat: 'Y-m-dTH:i:S',
        onReady: function (selectedDates, dateStr, instance) {
          if (instance.isMobile) {
            instance.mobileInput.setAttribute('step', null);
          }
        }
      });
    }

    // Inline sidebar calendar (flatpicker)
    if (inlineCalendar) {
      inlineCalInstance = inlineCalendar.flatpickr({
        monthSelectorType: 'static',
        static: true,
        inline: true,
        onChange: function(selectedDates, dateStr, instance) {
          if (selectedDates.length > 0) {
            const selectedDate = selectedDates[0];
            const dateData = {
              dateObject: selectedDate,
              dateString: dateStr,
              year: selectedDate.getFullYear(),
              month: selectedDate.getMonth() + 1,
              day: selectedDate.getDate(),
              isoString: selectedDate.toISOString(),
              localeString: selectedDate.toLocaleDateString()
            };          
            showEventsForDate(dateData.dateString);
          }
        }
      });
    }

    // Event click function
    function eventClick(info) {
      eventToUpdate = info.event;
      if (eventToUpdate.url) {
        info.jsEvent.preventDefault();
        window.open(eventToUpdate.url, '_blank');
      }
      bsAddEventSidebar.show();
      
      // For update event set offcanvas title text: Update Event
      if (offcanvasTitle) {
        offcanvasTitle.innerHTML = 'Update Event';
      }
      btnSubmit.innerHTML = 'Update';
      btnSubmit.classList.add('btn-update-event');
      btnSubmit.classList.remove('btn-add-event');
      btnDeleteEvent.classList.remove('d-none');

      eventTitle.value = eventToUpdate.title;
      
      // Set dates
      if (start && eventToUpdate.start) {
        start.setDate(eventToUpdate.start, true, 'Y-m-d');
      }
      
      if (allDaySwitch) {
        allDaySwitch.checked = eventToUpdate.allDay === true;
      }
    }

    // Modify sidebar toggler for responsive design
    function modifyToggler() {
      const fcSidebarToggleButton = document.querySelector('.fc-sidebarToggle-button');
      if (fcSidebarToggleButton) {
        fcSidebarToggleButton.classList.remove('fc-button-primary');
        fcSidebarToggleButton.classList.add('d-lg-none', 'd-inline-block', 'ps-0');
        while (fcSidebarToggleButton.firstChild) {
          fcSidebarToggleButton.firstChild.remove();
        }
        fcSidebarToggleButton.setAttribute('data-bs-toggle', 'sidebar');
        fcSidebarToggleButton.setAttribute('data-overlay', '');
        fcSidebarToggleButton.setAttribute('data-target', '#app-calendar-sidebar');
        fcSidebarToggleButton.insertAdjacentHTML(
          'beforeend',
          '<i class="icon-base ti tabler-menu-2 icon-lg text-heading"></i>'
        );
      }
    }

    // Filter events by calendar
    function selectedCalendars() {
      let selected = [],
        filterInputChecked = [].slice.call(document.querySelectorAll('.input-filter:checked'));

      filterInputChecked.forEach(item => {
        selected.push(item.getAttribute('data-value'));
      });

      return selected;
    }

    // Fetch Events
    function fetchEvents(info, successCallback, failureCallback) {
      fetch(`./functions/get_events.php`)
        .then(response => {
          if (!response.ok) {
            throw new Error('Network response was not ok');
          }
          return response.json();
        })
        .then(data => {
          console.log('Raw events data from PHP:', data);
          
          // Check what's in each event
          data.forEach((event, index) => {
            console.log(`Event ${index}:`, {
              id: event.id,
              title: event.event_name || event.title,
              start: event.event_date || event.start,
              status: event.status,
              hasStart: !!event.event_date || !!event.start,
              validStart: (event.event_date || event.start) && 
                         (event.event_date || event.start) !== 'null' && 
                         (event.event_date || event.start) !== null && 
                         (event.event_date || event.start) !== '0000-00-00'
            });
          });
          
          // Transform data for FullCalendar
          const transformedEvents = data.map(event => {
            // Use event_date if available, otherwise use start
            const startDate = event.event_date || event.start;
            const title = event.event_name || event.title || 'Untitled Event';
            const status = event.status || 'pending';
            
            // Determine color based on status
            const colorMap = {
              'pending': '#ffc107',
              'completed': '#28a745',
              'cancelled': '#dc3545',
              'confirmed': '#007bff',
              'ongoing': '#17a2b8',
              'progress': '#6f42c1'
            };
            
            const color = colorMap[status] || '#6c757d';
            
            return {
              id: event.id,
              title: title,
              start: startDate,
              allDay: true,
              extendedProps: {
                status: status,
                client_name: event.client_name,
                phone: event.mobile || event.phone,
                email: event.email,
                venue: event.venu_address,
                description: event.description,
                price: event.price
              },
              backgroundColor: color,
              borderColor: color,
              textColor: status === 'pending' ? '#000' : '#fff',
              className: `fc-event ${status}`
            };
          });
          
          // Filter out events without valid start dates
          const validEvents = transformedEvents.filter(event => {
            const hasValidStart = event.start && 
                                 event.start !== 'null' && 
                                 event.start !== null && 
                                 event.start !== '0000-00-00';
            
            if (!hasValidStart) {
              console.warn('Skipping event without valid start date:', event);
            }
            
            return hasValidStart;
          });
          
          console.log(`Valid events: ${validEvents.length} out of ${data.length}`);
          successCallback(validEvents);
        })
        .catch(function (error) {
          console.error('Error fetching events:', error);
          if (failureCallback) failureCallback(error);
        });
    }

    // Determine responsive initial view
    function getInitialView() {
      const w = window.innerWidth;
      if (w < 576) {
        return 'listMonth'; // Mobile: list view
      } else if (w < 768) {
        return 'dayGridWeek'; // Small tablet: week view
      } else if (w < 992) {
        return 'dayGridMonth'; // Tablet: month view
      } else {
        return 'dayGridMonth'; // Desktop: month view
      }
    }

    // Determine responsive header toolbar
    function getHeaderToolbar() {
      const w = window.innerWidth;
      if (w < 576) {
        return {
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,dayGridWeek,listMonth'
        };
      } else if (w < 768) {
        return {
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,dayGridWeek,dayGridDay'
        };
      } else {
        return {
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,dayGridWeek,dayGridDay,listMonth'
        };
      }
    }

    // Adjust calendar height based on screen size
    function getCalendarHeight() {
      const w = window.innerWidth;
      if (w < 576) {
        return 'auto';
      } else if (w < 768) {
        return 500;
      } else if (w < 992) {
        return 600;
      } else {
        return 700;
      }
    }

    // Adjust event display based on screen size
    function adjustEventDisplay() {
      const w = window.innerWidth;
      const calendarView = calendar.view;
      
      if (w < 576) {
        // Mobile: smaller events
        document.querySelectorAll('.fc-event').forEach(event => {
          event.style.fontSize = '0.7rem';
          event.style.padding = '1px 3px';
          event.style.margin = '1px 0';
        });
        
        // Adjust day cell height
        document.querySelectorAll('.fc-daygrid-day-frame').forEach(cell => {
          cell.style.minHeight = '60px';
        });
      } else if (w < 768) {
        // Tablet: medium events
        document.querySelectorAll('.fc-event').forEach(event => {
          event.style.fontSize = '0.8rem';
          event.style.padding = '2px 4px';
          event.style.margin = '1px 0';
        });
        
        document.querySelectorAll('.fc-daygrid-day-frame').forEach(cell => {
          cell.style.minHeight = '70px';
        });
      } else {
        // Desktop: normal events
        document.querySelectorAll('.fc-event').forEach(event => {
          event.style.fontSize = '0.9rem';
          event.style.padding = '3px 6px';
          event.style.margin = '2px 0';
        });
        
        document.querySelectorAll('.fc-daygrid-day-frame').forEach(cell => {
          cell.style.minHeight = '80px';
        });
      }
    }

    // Init FullCalendar with responsive settings
    let calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: getInitialView(),
      events: fetchEvents,
      plugins: [FullCalendar.dayGridPlugin, FullCalendar.interactionPlugin, 
                FullCalendar.listPlugin, FullCalendar.timeGridPlugin],
      editable: true,
      dragScroll: true,
      dayMaxEvents: 3, // Show up to 3 events per day
      eventResizableFromStart: true,
      customButtons: {
        sidebarToggle: {
          text: 'Sidebar'
        }
      },
      headerToolbar: getHeaderToolbar(),
      direction: direction,
      initialDate: new Date(),
      navLinks: true,
      height: getCalendarHeight(),
      contentHeight: 'auto',
      aspectRatio: window.innerWidth < 768 ? 1 : 1.8,
      eventClassNames: function ({ event: calendarEvent }) {
        const status = calendarEvent.extendedProps.status || 'default';
        const colorName = calendarColors[status] || calendarColors.default;
        return ['fc-event-' + status, 'border-start', 'border-3', 'border-' + colorName];
      },
      eventContent: function(arg) {
        // Custom event content for better display
        const title = arg.event.title;
        const status = arg.event.extendedProps.status;
        
        // Truncate title based on screen size
        let displayTitle = title;
        if (window.innerWidth < 576 && title.length > 15) {
          displayTitle = title.substring(0, 15) + '...';
        } else if (window.innerWidth < 768 && title.length > 20) {
          displayTitle = title.substring(0, 20) + '...';
        } else if (title.length > 30) {
          displayTitle = title.substring(0, 30) + '...';
        }
        
        // Status indicator dot
        const statusDot = document.createElement('span');
        statusDot.className = 'fc-event-dot me-1';
        statusDot.style.cssText = `
          display: inline-block;
          width: 8px;
          height: 8px;
          border-radius: 50%;
          background-color: ${arg.event.backgroundColor};
          vertical-align: middle;
        `;
        
        const titleEl = document.createElement('span');
        titleEl.textContent = displayTitle;
        titleEl.style.cssText = `
          vertical-align: middle;
          font-size: ${window.innerWidth < 576 ? '0.7rem' : '0.8rem'};
          line-height: 1.2;
          display: inline-block;
          max-width: ${window.innerWidth < 576 ? 'calc(100% - 10px)' : '100%'};
        `;
        
        const wrapper = document.createElement('div');
        wrapper.appendChild(statusDot);
        wrapper.appendChild(titleEl);
        wrapper.style.cssText = `
          display: flex;
          align-items: center;
          overflow: hidden;
          width: 100%;
        `;
        
        return { domNodes: [wrapper] };
      },
      eventClick: function (info) {
        eventClick(info);
      },
      dateClick: function (info) {
        let date = moment(info.date).format('YYYY-MM-DD');
        console.log('Date clicked:', date);
        
        // Fill the date in the form
        const input = document.querySelector('input[name="event_date"]');
        if (input) {
          input.value = date;
        }
        
        resetValues();
        bsAddEventSidebar.show();

        // For new event set offcanvas title text: Add Event
        if (offcanvasTitle) {
          offcanvasTitle.innerHTML = 'Add Event';
        }
        btnSubmit.innerHTML = 'Add';
        btnSubmit.classList.remove('btn-update-event');
        btnSubmit.classList.add('btn-add-event');
        btnDeleteEvent.classList.add('d-none');
        
        if (eventStartDate) {
          eventStartDate.value = date;
        }
      },
      datesSet: function () {
        modifyToggler();
        adjustEventDisplay();
      },
      viewDidMount: function () {
        modifyToggler();
        adjustEventDisplay();
      },
      // Handle window resize
      windowResize: function(view) {
        calendar.setOption('height', getCalendarHeight());
        calendar.setOption('headerToolbar', getHeaderToolbar());
        adjustEventDisplay();
      }
    });

    // Render calendar
    calendar.render();

    // Modify sidebar toggler
    modifyToggler();

    // Form Validation
    const eventForm = document.getElementById('event-form');
    if (eventForm) {
      const fv = FormValidation.formValidation(eventForm, {
        fields: {
          eventTitle: {
            validators: {
              notEmpty: {
                message: 'Please enter event title'
              }
            }
          },
          eventStartDate: {
            validators: {
              notEmpty: {
                message: 'Please enter start date'
              }
            }
          },
        },
        plugins: {
          trigger: new FormValidation.plugins.Trigger(),
          bootstrap5: new FormValidation.plugins.Bootstrap5({
            eleValidClass: '',
            rowSelector: function (field, ele) {
              return '.form-control-validation';
            }
          }),
          submitButton: new FormValidation.plugins.SubmitButton(),
          autoFocus: new FormValidation.plugins.AutoFocus()
        }
      })
      .on('core.form.valid', function () {
        isFormValid = true;
      })
      .on('core.form.invalid', function () {
        isFormValid = true;
      });
    }

    // Mobile: move left sidebar into an offcanvas drawer when opened
    const mobileOpenSidebarBtn = document.getElementById('mobile-open-sidebar');
    const mobileCalendarSidebar = document.getElementById('mobileCalendarSidebar');
    let bsMobileSidebar = null;
    let originalSidebarParent = null;
    if (mobileCalendarSidebar) {
      bsMobileSidebar = new bootstrap.Offcanvas(mobileCalendarSidebar);
    }

    if (mobileOpenSidebarBtn && mobileCalendarSidebar && appCalendarSidebar) {
      originalSidebarParent = appCalendarSidebar.parentNode;
      mobileOpenSidebarBtn.addEventListener('click', function () {
        const body = mobileCalendarSidebar.querySelector('.offcanvas-body');
        // move the sidebar into offcanvas body
        body.appendChild(appCalendarSidebar);
        bsMobileSidebar.show();
      });

      mobileCalendarSidebar.addEventListener('hidden.bs.offcanvas', function () {
        // move sidebar back to original parent
        if (originalSidebarParent) {
          originalSidebarParent.appendChild(appCalendarSidebar);
        }
      });
    }

    // Add Event to calendar
    function addEvent(eventData) {
      currentEvents.push(eventData);
      calendar.refetchEvents();
    }

    // Update Event
    function updateEvent(eventData) {
      eventData.id = parseInt(eventData.id);
      const index = currentEvents.findIndex(el => el.id === eventData.id);
      if (index !== -1) {
        currentEvents[index] = eventData;
      }
      calendar.refetchEvents();
    }

    // Remove Event
    function removeEvent(eventId) {
      currentEvents = currentEvents.filter(function (event) {
        return event.id != eventId;
      });
      calendar.refetchEvents();
    }

    // Add new event button handler
    if (btnSubmit) {
      btnSubmit.addEventListener('click', e => {
        e.preventDefault();
        
        // Form submission will be handled by the form itself
        eventForm.submit();
      });
    }

    // Delete event button handler
    if (btnDeleteEvent) {
      btnDeleteEvent.addEventListener('click', e => {
        if (confirm('Are you sure you want to delete this event?')) {
          if (eventToUpdate) {
            removeEvent(eventToUpdate.id);
            bsAddEventSidebar.hide();
            showToast('Event deleted successfully!', 'success');
          }
        }
      });
    }

    // Reset event form inputs values
    function resetValues() {
      if (eventUrl) eventUrl.value = '';
      if (eventStartDate) eventStartDate.value = '';
      if (eventTitle) eventTitle.value = '';
      if (eventLocation) eventLocation.value = '';
      if (allDaySwitch) allDaySwitch.checked = false;
      if (eventGuests.length) eventGuests.val('').trigger('change');
      if (eventDescription) eventDescription.value = '';
    }

    // When modal hides reset input values
    if (addEventSidebar) {
      addEventSidebar.addEventListener('hidden.bs.offcanvas', function () {
        resetValues();
      });
    }

    // Accessibility: focus management for add-event offcanvas/bottom-sheet
    if (addEventSidebar) {
      addEventSidebar.addEventListener('shown.bs.offcanvas', function () {
        // focus first form control inside the offcanvas for keyboard users
        const firstControl = addEventSidebar.querySelector('input, select, textarea, button');
        if (firstControl) {
          try { firstControl.focus(); } catch (e) { /* ignore */ }
        }
      });

      addEventSidebar.addEventListener('hidden.bs.offcanvas', function () {
        // return focus to the calendar or menu button for context
        const mobileBtn = document.getElementById('mobile-open-sidebar');
        if (mobileBtn) {
          try { mobileBtn.focus(); } catch (e) { /* ignore */ }
        }
      });
    }

    // Hide left sidebar if the right sidebar is open
    if (btnToggleSidebar) {
      btnToggleSidebar.addEventListener('click', e => {
        if (offcanvasTitle) {
          offcanvasTitle.innerHTML = 'Add Event';
        }
        if (btnSubmit) {
          btnSubmit.innerHTML = 'Add';
          btnSubmit.classList.remove('btn-update-event');
          btnSubmit.classList.add('btn-add-event');
        }
        if (btnDeleteEvent) btnDeleteEvent.classList.add('d-none');
        if (appCalendarSidebar) appCalendarSidebar.classList.remove('show');
        if (appOverlay) appOverlay.classList.remove('show');
      });
    }

    // Calendar filter functionality
    if (selectAll) {
      selectAll.addEventListener('click', e => {
        const isChecked = e.currentTarget.checked;
        document.querySelectorAll('.input-filter').forEach(c => c.checked = isChecked);
        calendar.refetchEvents();
      });
    }

    if (filterInputs.length > 0) {
      filterInputs.forEach(item => {
        item.addEventListener('click', () => {
          const checkedCount = document.querySelectorAll('.input-filter:checked').length;
          const totalCount = document.querySelectorAll('.input-filter').length;
          if (selectAll) {
            selectAll.checked = checkedCount === totalCount;
          }
          calendar.refetchEvents();
        });
      });
    }
    
    if (inlineCalInstance) {
      inlineCalInstance.config.onChange.push(function (date) {
        calendar.changeView(calendar.view.type, moment(date[0]).format('YYYY-MM-DD'));
        modifyToggler();
        if (appCalendarSidebar) appCalendarSidebar.classList.remove('show');
        if (appOverlay) appOverlay.classList.remove('show');
      });
    }
    
    // Toast notification function
    function showToast(message, type = 'success') {
      const toastContainer = document.getElementById('toastContainer') || createToastContainer();
      const toastId = 'toast-' + Date.now();
      
      const toast = document.createElement('div');
      toast.className = `bs-toast toast fade ${type === 'success' ? 'bg-success' : 'bg-danger'}`;
      toast.id = toastId;
      toast.setAttribute('role', 'alert');
      toast.setAttribute('aria-live', 'assertive');
      toast.setAttribute('aria-atomic', 'true');
      
      toast.innerHTML = `
        <div class="toast-header">
          <i class="bx bx-bell me-2"></i>
          <div class="me-auto fw-semibold">${type === 'success' ? 'Success' : 'Error'}</div>
          <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">${message}</div>
      `;
      
      toastContainer.appendChild(toast);
      const bsToast = new bootstrap.Toast(toast);
      bsToast.show();
      
      toast.addEventListener('hidden.bs.toast', function () {
        toast.remove();
      });
    }

    // Show events for specific date
    function showEventsForDate(dateString) {
      const modal = new bootstrap.Modal(document.getElementById('dateEventsModal'));
      const selectedDateSpan = document.getElementById('selectedDate');
      const eventsContainer = document.getElementById('eventsContainer');
      
      // Format date for display
      const date = new Date(dateString);
      const formattedDate = date.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
      
      selectedDateSpan.textContent = formattedDate;
      
      // Show loading
      eventsContainer.innerHTML = `
        <div class="text-center py-5">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mt-2 text-muted">Loading events...</p>
        </div>
      `;
      
      // Show modal
      modal.show();
      
      // Fetch events for this date
      fetch(`./functions/get_events_by_date.php?date=${dateString}`)
        .then(response => {
          if (!response.ok) {
            throw new Error('Network response was not ok');
          }
          return response.json();
        })
        .then(events => {
          if (events.error) {
            throw new Error(events.error);
          }
          
          console.log('Events for date', dateString, ':', events);
          
          if (events.length === 0) {
            eventsContainer.innerHTML = `
              <div class="text-center py-5">
                <div class="mb-3">
                  <i class="bi bi-calendar-x display-4 text-muted"></i>
                </div>
                <h5 class="text-muted">No Events</h5>
                <p class="text-muted">No events scheduled for this date.</p>
                <button class="btn btn-primary mt-3" onclick="addEventForDate('${dateString}')">
                  <i class="bi bi-plus-circle me-1"></i> Add Event
                </button>
              </div>
            `;
            return;
          }
          
          // Render events
          let eventsHTML = '';
          events.forEach(event => {
            // Determine status color
            const statusColors = {
              'pending': 'warning',
              'ongoing': 'info',
              'progress': 'secondary',
              'completed': 'success',
              'cancelled': 'danger',
              'confirmed': 'primary'
            };
            
            const status = event.status || 'pending';
            const statusColor = statusColors[status.toLowerCase()] || 'secondary';
            
            // Format event date
            const eventDate = event.event_date ? 
              new Date(event.event_date).toLocaleDateString('en-US', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
              }) : 'N/A';
            
            // Truncate venue address if too long
            const venue = event.venu_address || '';
            let truncatedVenue = venue;
            if (window.innerWidth < 768 && venue.length > 50) {
              truncatedVenue = venue.substring(0, 50) + '...';
            } else if (venue.length > 80) {
              truncatedVenue = venue.substring(0, 80) + '...';
            }
            
            // Truncate description
            const description = event.description || '';
            let truncatedDescription = description;
            if (window.innerWidth < 768 && description.length > 60) {
              truncatedDescription = description.substring(0, 60) + '...';
            } else if (description.length > 100) {
              truncatedDescription = description.substring(0, 100) + '...';
            }
            
            eventsHTML += `
              <div class="card mb-3 shadow-sm border-start border-3 border-${statusColor}">
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="flex-grow-1">
                      <h6 class="card-title mb-1">
                        <i class="bi bi-calendar-event me-1"></i>
                        ${event.event_name || 'Untitled Event'}
                      </h6>
                      <small class="text-muted">
                        <i class="bi bi-calendar me-1"></i>
                        ${eventDate}
                      </small>
                    </div>
                    <div>
                      <span class="badge bg-${statusColor}">
                        ${status.charAt(0).toUpperCase() + status.slice(1)}
                      </span>
                    </div>
                  </div>
                  
                  ${event.client_name ? `
                  <div class="mb-1">
                    <i class="bi bi-person me-1 text-secondary"></i>
                    <small class="text-muted">${event.client_name}</small>
                  </div>
                  ` : ''}
                  
                  ${venue ? `
                  <div class="mb-1">
                    <i class="bi bi-geo-alt me-1 text-secondary"></i>
                    <small class="text-muted">${truncatedVenue}</small>
                  </div>
                  ` : ''}
                  
                  ${event.phone ? `
                  <div class="mb-1">
                    <i class="bi bi-telephone me-1 text-success"></i>
                    <small class="text-muted">${event.phone}</small>
                  </div>
                  ` : ''}
                  
                  ${description ? `
                  <div class="mb-3">
                    <small class="text-muted d-block mb-1">
                      <i class="bi bi-card-text"></i> Description
                    </small>
                    <small class="d-block">${truncatedDescription}</small>
                  </div>
                  ` : ''}
                  
                  <div class="d-flex gap-2 flex-wrap">
                    ${event.phone ? `
                    <a href="tel:${event.phone}" 
                       class="btn btn-sm btn-outline-success"
                       title="Call">
                      <i class="bi bi-telephone-fill"></i>
                    </a>
                    ` : ''}
                    
                    ${event.email ? `
                    <a href="mailto:${event.email}" 
                       class="btn btn-sm btn-outline-primary"
                       title="Email">
                      <i class="bi bi-envelope-fill"></i>
                    </a>
                    ` : ''}
                    
                    <a href="view_event.php?id=${event.id}" 
                       class="btn btn-sm btn-outline-info"
                       title="View Details">
                      <i class="bi bi-eye-fill"></i>
                    </a>
                    
                    <a href="edit_event.php?id=${event.id}" 
                       class="btn btn-sm btn-outline-warning"
                       title="Edit">
                      <i class="bi bi-pencil-square"></i>
                    </a>
                  </div>
                </div>
              </div>
            `;
          });
          
          eventsContainer.innerHTML = eventsHTML;
        })
        .catch(error => {
          console.error('Error fetching events:', error);
          eventsContainer.innerHTML = `
            <div class="alert alert-danger">
              <i class="bi bi-exclamation-triangle me-2"></i>
              Error loading events: ${error.message}
            </div>
          `;
        });
    }

    // Function to add event for specific date
    window.addEventForDate = function(dateString) {
      // Close the date events modal
      const dateModal = document.getElementById('dateEventsModal');
      const modalInstance = bootstrap.Modal.getInstance(dateModal);
      if (modalInstance) {
        modalInstance.hide();
      }
      
      // Open the add event sidebar with pre-filled date
      if (bsAddEventSidebar) {
        bsAddEventSidebar.show();
        if (offcanvasTitle) {
          offcanvasTitle.innerHTML = 'Add Event';
        }
        if (btnSubmit) {
          btnSubmit.innerHTML = 'Add';
          btnSubmit.classList.remove('btn-update-event');
          btnSubmit.classList.add('btn-add-event');
        }
        if (btnDeleteEvent) btnDeleteEvent.classList.add('d-none');
        
        // Set the date in the form
        if (eventStartDate) eventStartDate.value = dateString;
      }
    };

    // Function to delete event
    window.deleteEvent = function(eventId, eventName) {
      if (confirm(`Are you sure you want to delete "${eventName}"?`)) {
        fetch(`./functions/delete_event.php?id=${eventId}`, {
          method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            showToast('Event deleted successfully!', 'success');
            // Refresh the calendar
            calendar.refetchEvents();
            // Close modal and reopen to refresh events
            const modal = bootstrap.Modal.getInstance(document.getElementById('dateEventsModal'));
            if (modal) {
              modal.hide();
            }
          } else {
            showToast('Error deleting event: ' + data.error, 'error');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          showToast('Error deleting event', 'error');
        });
      }
    };
    
    // Create toast container if it doesn't exist
    function createToastContainer() {
      const container = document.createElement('div');
      container.id = 'toastContainer';
      container.className = 'toast-container position-fixed top-0 end-0 p-3';
      container.style.cssText = `
        z-index: 9999;
        max-width: ${window.innerWidth < 576 ? '100%' : '350px'};
      `;
      document.body.appendChild(container);
      return container;
    }

    // Handle window resize for responsive adjustments
    let resizeTimer;
    window.addEventListener('resize', function () {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(function () {
        // Update calendar view if needed
        const newView = getInitialView();
        if (calendar.view.type !== newView) {
          calendar.changeView(newView);
        }
        
        // Update calendar height
        calendar.setOption('height', getCalendarHeight());
        
        // Update header toolbar
        calendar.setOption('headerToolbar', getHeaderToolbar());
        
        // Adjust event display
        adjustEventDisplay();
        
        // Update sidebar toggler
        modifyToggler();
        
        // Update calendar size
        calendar.updateSize();
      }, 250);
    });

    // Initialize with proper sizing
    setTimeout(function() {
      adjustEventDisplay();
      calendar.updateSize();
    }, 100);

    // Add mobile-specific event listeners
    if (window.innerWidth < 768) {
      // Make calendar events more touch-friendly on mobile
      document.addEventListener('click', function(e) {
        if (e.target.closest('.fc-event')) {
          // Add a small delay to prevent accidental clicks
          e.preventDefault();
          setTimeout(() => {
            const eventElement = e.target.closest('.fc-event');
            const eventId = eventElement.getAttribute('data-event-id');
            if (eventId) {
              window.location.href = `view_event.php?id=${eventId}`;
            }
          }, 200);
        }
      });
    }

  })();

});