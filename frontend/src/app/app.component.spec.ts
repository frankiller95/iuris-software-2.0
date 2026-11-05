import { TestBed } from '@angular/core/testing';
import { provideRouter } from '@angular/router';
import { MessageService, ConfirmationService } from 'primeng/api';
import { AppComponent } from './app.component';

describe('AppComponent', () => {
  it('should create the app', async () => {
    await TestBed.configureTestingModule({
      imports: [AppComponent],
      providers: [provideRouter([]), MessageService, ConfirmationService],
    }).compileComponents();
    const fixture = TestBed.createComponent(AppComponent);
    expect(fixture.componentInstance).toBeTruthy();
  });
});
