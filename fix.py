from pathlib import Path

  p = Path.home() / ".codex" / "config.toml"
  text = p.read_text() if p.exists() else ""

  lines = text.splitlines()
  out = []
  skip = False

  for line in lines:
      if line.strip() == "[sandbox_workspace_write]":
          skip = True
          continue
      if skip and line.startswith("[") and line.strip().endswith("]"):
          skip = False
      if not skip:
          out.append(line)

  out.append("")
  out.append("[sandbox_workspace_write]")
  out.append('writable_roots = ["/var/www/html/events.computer-wizard.com.au", "/var/www/html/events.computer-wizard.com.au/.git"]')
  out.append("network_access = false")

  p.write_text("\n".join(out).strip() + "\n")
