<h2>New appointment request</h2>
<p>Reference: {{ $appointment->reference }}</p>
<p>A new request was received and is awaiting clinic follow-up. It is not a confirmed appointment.</p>
<p>Preferred date: {{ $appointment->preferred_date->format('d M Y') }}</p>
<p>Preferred time: {{ $appointment->preferred_time ? substr($appointment->preferred_time, 0, 5) : 'No preference' }}</p>
