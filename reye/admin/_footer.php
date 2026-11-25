  </main>
</div>
<script>
// Ask before deleting anything
document.querySelectorAll('form[data-confirm]').forEach(f => f.addEventListener('submit', e => {
  if (!confirm(f.dataset.confirm)) e.preventDefault();
}));
</script>
</body>
</html>
